<?php

namespace Goodby\EventSourcing;

use Goodby\Assertion\Assert;

final class DispatchableEvent
{
    /**
     * @var string
     */
    private $eventId;

    /**
     * @var Event
     */
    private $event;

    /**
     * @param string $eventId
     * @param Event $event
     */
    public function __construct($eventId, Event $event)
    {
        Assert::argumentNotEmpty($eventId, 'Event ID is required');

        $this->eventId = $eventId;
        $this->event = $event;
    }

    /**
     * @return string
     */
    public function eventId()
    {
        return $this->eventId;
    }

    /**
     * @return Event
     */
    public function event()
    {
        return $this->event;
    }
}
