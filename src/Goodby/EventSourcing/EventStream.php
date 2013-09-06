<?php

namespace Goodby\EventSourcing;

use Goodby\Assertion\Assert;

final class EventStream
{
    /**
     * @var string
     */
    private $streamVersion;

    /**
     * @var Event[]
     */
    private $events = [];

    /**
     * @param int $streamVersion
     * @param Event[] $events
     */
    public function __construct($streamVersion, array $events)
    {
        Assert::argumentAtLeast($streamVersion, 1, 'Stream version must be 1 at least');
        Assert::argumentLengthAtLeast($events, 1, 'Events list must NOT be empty');

        $this->streamVersion = $streamVersion;

        array_walk($events, function (Event $event) {
            $this->events[] = $event;
        });
    }

    /**
     * @return int
     */
    public function streamVersion()
    {
        return $this->streamVersion;
    }

    /**
     * @return Event[]
     */
    public function events()
    {
        return $this->events;
    }
}
