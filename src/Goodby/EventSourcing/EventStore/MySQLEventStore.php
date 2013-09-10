<?php

namespace Goodby\EventSourcing\EventStore;

use Exception;
use Goodby\Assertion\Assert;
use Goodby\EventSourcing\DispatchableEvent;
use Goodby\EventSourcing\Event;
use Goodby\EventSourcing\EventNotifiable;
use Goodby\EventSourcing\EventSerializer;
use Goodby\EventSourcing\EventStore;
use Goodby\EventSourcing\EventStream;
use Goodby\EventSourcing\EventStreamId;
use Goodby\EventSourcing\Exception\EventSourcingException;
use Goodby\EventSourcing\Exception\EventStreamNotFoundException;
use PDO;
use PDOStatement;

class MySQLEventStore implements EventStore
{
    /**
     * @var PDO
     */
    private $connection;

    /**
     * @var EventSerializer
     */
    private $serializer;

    /**
     * @var EventNotifiable
     */
    private $notifiable;

    /**
     * @var string
     */
    private $tableName;

    public function __construct(PDO $connection, EventSerializer $serializer, $tableName = 'event_store')
    {
        Assert::argumentNotEmpty($tableName, 'Table name is required.');

        $this->connection = $connection;
        $this->serializer = $serializer;
        $this->tableName = $tableName;
    }

    /**
     * @param EventStreamId $eventStreamId
     * @param Event[] $events
     * @throws
     * @return void
     */
    public function append(EventStreamId $eventStreamId, array $events)
    {
        Assert::argumentLengthAtLeast($events, 1, 'At least one event is required');

        try {
            $this->connection->beginTransaction();

            $statement = $this->connection->prepare(
                sprintf(
                    'INSERT INTO `%s` (stream_name, stream_version, event_type, event_body) '
                    . 'VALUES (:stream_name, :stream_version, :event_type, :event_body)',
                    $this->tableName
                )
            );

            $streamIndex = 0;

            foreach ($events as $event) {
                $this->appendEvent(
                    $statement,
                    $eventStreamId->streamName(),
                    $eventStreamId->streamVersion() + $streamIndex,
                    $event
                );
                $streamIndex += 1;
            }

            $this->connection->commit();
        } catch (Exception $because) {
            $this->connection->rollBack();
            throw EventSourcingException::failedToAppend($because);
        }

        if ($this->notifiable) {
            $this->notifiable->notifyEvents();
        }
    }

    /**
     * @param EventStreamId $eventStreamId
     * @return EventStream
     * @throws EventStreamNotFoundException
     * @throws EventSourcingException
     */
    public function eventStreamSince(EventStreamId $eventStreamId)
    {
        try {
            $statement = $this->connection->prepare(
                sprintf(
                    'SELECT stream_version, event_type, event_body FROM %s '
                    .'WHERE stream_name = :stream_name AND stream_version >= :stream_version '
                    .'ORDER BY stream_version',
                    $this->tableName
                )
            );

            $statement->bindValue(':stream_name', $eventStreamId->streamName(), PDO::PARAM_STR);
            $statement->bindValue(':stream_version', $eventStreamId->streamVersion(), PDO::PARAM_INT);
            $statement->execute();

            if ($statement->rowCount() === 0) {
                throw EventStreamNotFoundException::noStream($eventStreamId);
            }

            $eventStream = $this->buildEventStream($statement);

            return $eventStream;
        } catch (EventStreamNotFoundException $e) {
            throw $e; // escalation
        } catch (Exception $because) {
            throw EventSourcingException::cannotQueryEventStream($eventStreamId, $because);
        }
    }

    /**
     * @param string $lastDispatchedEventId
     * @throws \Exception
     * @return DispatchableEvent[]
     */
    public function dispatchableEventsSince($lastDispatchedEventId)
    {
        try {
            $statement = $this->connection->prepare(
                "SELECT event_id, event_body, event_type FROM {$this->tableName} WHERE event_id > :event_id ORDER BY event_id"
            );
            $statement->bindValue(':event_id', $lastDispatchedEventId);
            $statement->execute();

            $events = [];

            foreach ($statement as $result) {
                $events[] = new DispatchableEvent(
                    $result['event_id'],
                    $this->deserializeEvent($result['event_type'], $result['event_body'])
                );
            }

            return $events;
        } catch (Exception $because) {
            throw EventSourcingException::cannotQueryDispatchableEventsSince($lastDispatchedEventId, $because);
        }
    }

    /**
     * Drop all events from event store.
     * Mainly used for testing.
     * @return void
     */
    public function purge()
    {
        $this->connection->exec('TRUNCATE TABLE '.$this->tableName);
    }

    /**
     * @param EventNotifiable $eventNotifiable
     * @return void
     */
    public function registerEventNotifiable(EventNotifiable $eventNotifiable)
    {
        $this->notifiable = $eventNotifiable;
    }

    /**
     * @param PDOStatement $statement
     * @param string $streamName
     * @param int $streamVersion
     * @param Event $event
     */
    private function appendEvent(PDOStatement $statement, $streamName, $streamVersion, Event $event)
    {
        $statement->bindValue(':stream_name', $streamName, PDO::PARAM_STR);
        $statement->bindValue(':stream_version', $streamVersion, PDO::PARAM_INT);
        $statement->bindValue(':event_type', strtr(get_class($event), '\\', '.'), PDO::PARAM_STR);
        $statement->bindValue(':event_body', $this->serializer->serialize($event), PDO::PARAM_STR);
        $statement->execute();
    }

    /**
     * @param PDOStatement $resultSet
     * @return EventStream
     */
    private function buildEventStream(PDOStatement $resultSet)
    {
        $version = 0;
        $events = [];

        foreach ($resultSet as $result) {
            $version = $result['stream_version'];
            $events[] = $this->deserializeEvent($result['event_type'], $result['event_body']);
        }

        return new EventStream($version, $events);
    }

    /**
     * @param string $eventType
     * @param string $eventBody
     * @return Event
     */
    private function deserializeEvent($eventType, $eventBody)
    {
        return $this->serializer->deserialize(
            strtr($eventType, '.', '\\'),
            $eventBody
        );
    }
}
