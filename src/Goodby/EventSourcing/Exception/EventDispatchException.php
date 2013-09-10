<?php

namespace Goodby\EventSourcing\Exception;

use Exception;

class EventDispatchException extends \RuntimeException
{
    /**
     * @param Exception $because
     * @return EventDispatchException
     */
    public static function cannotQueryLastDispatchedEvent(Exception $because)
    {
        return new self(
            sprintf('Cannot query last dispatched event because: %s', $because->getMessage()),
            null,
            $because
        );
    }

    /**
     * @param Exception $because
     * @return EventDispatchException
     */
    public static function cannotUpdateDispatcherLastEvent(Exception $because)
    {
        return new self(
            sprintf('Cannot update dispatcher last event because: %s', $because->getMessage()),
            null,
            $because
        );
    }

    /**
     * @param Exception $because
     * @return EventDispatchException
     */
    public static function cannotDispatchEvents(Exception $because)
    {
        return new self(
            sprintf("Cannot dispatch events because: %s", $because->getMessage()),
            null,
            $because
        );
    }
}
