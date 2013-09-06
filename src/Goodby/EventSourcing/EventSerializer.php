<?php

namespace Goodby\EventSourcing;

use Goodby\EventSourcing\Exception\EventSourcingException;

interface EventSerializer
{
    /**
     * @param Event $event
     * @return string
     * @throws EventSourcingException when failed to serialize
     */
    public function serialize(Event $event);

    /**
     * @param string $eventClassName
     * @param string $serialization
     * @return Event
     * @throws EventSourcingException when failed to deserialize
     */
    public function deserialize($eventClassName, $serialization);
}
