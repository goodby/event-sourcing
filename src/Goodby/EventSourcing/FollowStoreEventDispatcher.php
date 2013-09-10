<?php

namespace Goodby\EventSourcing;

use Exception;
use Goodby\EventSourcing\Exception\EventDispatchException;
use PDO;

class FollowStoreEventDispatcher implements EventNotifiable, EventDispatcher
{
    /**
     * @var EventDispatcher[]
     */
    private $dispatchers = [];

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var PDO
     */
    private $connection;

    /**
     * @param EventStore $eventStore
     * @param PDO $connection
     */
    public function __construct(EventStore $eventStore, PDO $connection)
    {
        $this->eventStore = $eventStore;
        $this->connection = $connection;
    }

    /**
     * @param DispatchableEvent $dispatchableEvent
     * @return void
     */
    public function dispatch(DispatchableEvent $dispatchableEvent)
    {
        foreach ($this->dispatchers as $dispatcher) {
            $dispatcher->dispatch($dispatchableEvent);
        }
    }

    /**
     * @param EventDispatcher $eventDispatcher
     * @return void
     */
    public function registerEventDispatcher(EventDispatcher $eventDispatcher)
    {
        $this->dispatchers[] = $eventDispatcher;
    }

    /**
     * @param DispatchableEvent $dispatchableEvent
     * @return bool
     */
    public function understands(DispatchableEvent $dispatchableEvent)
    {
        return true;
    }


    /**
     * @throws EventDispatchException
     */
    public function notifyEvents()
    {
        try {
            $this->connection->beginTransaction();

            $undispatchedEvents = $this->eventStore->dispatchableEventsSince($this->lastDispatchedEventId());

            if (count($undispatchedEvents) === 0) {
                return; // nothing to do
            }

            foreach ($undispatchedEvents as $event) {
                $this->dispatch($event);
            }

            $withLastEventId = $undispatchedEvents[count($undispatchedEvents) - 1];

            $this->setLastDispatchedEventId($withLastEventId->eventId());

            $this->connection->commit();
        } catch (EventDispatchException $e) {
            throw $e;
        } catch (Exception $because) {
            throw EventDispatchException::cannotDispatchEvents($because);
        }
    }

    /**
     * @return int
     * @throws EventDispatchException
     */
    private function lastDispatchedEventId()
    {
        try {
            $result = $this->connection->query('SELECT MAX(event_id) FROM tbl_dispatcher_last_event FOR UPDATE');

            $lastDispatchedEventId = $result->fetchColumn(0);

            if ($lastDispatchedEventId === null) {
                return 0;
            }

            return intval($lastDispatchedEventId);
        } catch (Exception $because) {
            throw EventDispatchException::cannotQueryLastDispatchedEvent($because);
        }
    }

    /**
     * @param int $lastDispatchedEventId
     * @throws EventDispatchException
     */
    private function setLastDispatchedEventId($lastDispatchedEventId)
    {
        $statement = null;

        try {
            $statement = $this->connection->prepare('UPDATE tbl_dispatcher_last_event SET event_id = :event_id');
            $statement->execute([':event_id' => $lastDispatchedEventId]);
            $updated = $statement->rowCount();
        } catch (Exception $because) {
            throw EventDispatchException::cannotUpdateDispatcherLastEvent($because);
        }

        if ($updated == 0) {
            try {
                $statement = $this->connection->prepare('INSERT INTO tbl_dispatcher_last_event VALUES(:event_id)');
                $statement->execute([':event_id' => $lastDispatchedEventId]);
            } catch (Exception $because) {
                throw EventDispatchException::cannotUpdateDispatcherLastEvent($because);
            }
        }
    }

}
