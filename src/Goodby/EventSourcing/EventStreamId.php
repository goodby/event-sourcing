<?php

namespace Goodby\EventSourcing;

use Goodby\Assertion\Assert;

final class EventStreamId
{
    /**
     * @var string
     */
    private $streamName;

    /**
     * @var int
     */
    private $streamVersion;

    /**
     * @param string $streamName
     * @param int $streamVersion
     */
    public function __construct($streamName, $streamVersion = 1)
    {
        Assert::argumentNotEmpty($streamName, 'Stream name is required');
        Assert::argumentNotEmpty($streamVersion, 'Stream version is required');

        $this->streamName = $streamName;
        $this->streamVersion = $streamVersion;
    }

    /**
     * @return string
     */
    public function streamName()
    {
        return $this->streamName;
    }

    /**
     * @return int
     */
    public function streamVersion()
    {
        return $this->streamVersion;
    }
}
