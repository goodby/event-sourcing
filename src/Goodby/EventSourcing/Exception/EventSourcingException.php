<?php

namespace Goodby\EventSourcing\Exception;

use Exception;
use Goodby\EventSourcing\EventStreamId;

class EventSourcingException extends \RuntimeException
{
    /**
     * @param EventStreamId $eventStreamId
     * @param Exception $because
     * @return EventSourcingException
     */
    public static function cannotQueryEventStream(EventStreamId $eventStreamId, Exception $because)
    {
        return new self(
            sprintf(
                'Cannot query event stream for: %s since version: %s because: %s',
                $eventStreamId->streamName(),
                $eventStreamId->streamVersion(),
                $because->getMessage()
            ),
            null,
            $because
        );
    }

    /**
     * @param Exception $because
     * @return EventSourcingException
     */
    public static function failedToAppend(Exception $because)
    {
        return new self(
            sprintf('Could not append to event store, because: %s', $because->getMessage()),
            null,
            $because
        );
    }

    /**
     * @param string $reason
     * @return EventSourcingException
     */
    public static function failedToSerializeEvent($reason)
    {
        return new self(
            sprintf('Could not serialize event, because: %s', $reason)
        );
    }

    /**
     * @param string $reason
     * @return EventSourcingException
     */
    public static function failedToDeserializeEvent($reason)
    {
        return new self(
            sprintf('Could not deserialize event, because: %s', $reason)
        );
    }
}
