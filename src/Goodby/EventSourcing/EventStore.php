<?php

namespace Goodby\EventSourcing;

use Goodby\EventSourcing\Exception\EventSourcingException;
use Goodby\EventSourcing\Exception\EventStreamNotFoundException;

interface EventStore
{
    /**
     * @param EventStreamId $eventStreamId
     * @param Event[] $events
     * @throws EventSourcingException
     * @return void
     */
    public function append(EventStreamId $eventStreamId, array $events);

    /**
     * @param EventStreamId $eventStreamId
     * @throws EventStreamNotFoundException
     * @throws EventSourcingException
     * @return EventStream
     */
    public function eventStreamSince(EventStreamId $eventStreamId);

    /**
     * @param EventNotifiable $eventNotifiable
     * @return void
     */
    public function registerEventNotifiable(EventNotifiable $eventNotifiable);
}
