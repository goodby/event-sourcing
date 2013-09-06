<?php

namespace Goodby\EventSourcing;

interface Event
{
    /**
     * @return int
     */
    public function eventVersion();

    /**
     * @return \DateTime
     */
    public function occurredOn();

    /**
     * Must return a primitive key-value set which is serializable.
     * @return mixed[]
     */
    public function toContractualData();

    /**
     * @param mixed[] $data
     * @return Event
     */
    public static function fromContractualData(array $data);
}
