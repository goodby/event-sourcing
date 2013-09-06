<?php

namespace Goodby\EventSourcing\EventSerializer;

use Goodby\Assertion\Assert;
use Goodby\EventSourcing\Event;
use Goodby\EventSourcing\EventSerializer;
use Goodby\EventSourcing\Exception\EventSourcingException;

class JSONEventSerializer implements EventSerializer
{
    /**
     * @param Event $event
     * @return string
     * @throws EventSourcingException when failed to serialize
     */
    public function serialize(Event $event)
    {
        $serialization = json_encode($event->toContractualData(), JSON_UNESCAPED_UNICODE);

        if ($serialization === false) {
            throw EventSourcingException::failedToSerializeEvent($this->getLastErrorMessage());
        }

        return $serialization;
    }

    /**
     * @param string $eventClassName
     * @param string $serialization
     * @return Event
     * @throws EventSourcingException when failed to deserialize
     */
    public function deserialize($eventClassName, $serialization)
    {
        Assert::argumentNotEmpty($eventClassName, 'Event class name is required');
        Assert::argumentClassExists($eventClassName, 'Event class does not exist: %class%');
        Assert::argumentSubclassOf($eventClassName, 'Goodby\EventSourcing\Event', 'Event class must be subclass of %parent%');

        $data = json_decode($serialization, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw EventSourcingException::failedToDeserializeEvent($this->getLastErrorMessage());
        }

        /** @var $eventClassName Event */
        return $eventClassName::fromContractualData($data);
    }

    /**
     * @return null|string
     */
    private function getLastErrorMessage()
    {
        switch (json_last_error()) {
            default:
                return null;
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';
            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch';
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON';
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
        }
    }
}
