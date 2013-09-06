<?php

namespace Goodby\EventSourcing\Exception;

use Goodby\EventSourcing\EventStreamId;

class EventStreamNotFoundException extends EventSourcingException
{
    /**
     * @param EventStreamId $eventStreamId
     * @return EventStreamNotFoundException
     */
    public static function noStream(EventStreamId $eventStreamId)
    {
        return new self(
            sprintf(
                'There is no such event stream: %s : %s',
                $eventStreamId->streamName(),
                $eventStreamId->streamVersion()
            )
        );
    }
}
