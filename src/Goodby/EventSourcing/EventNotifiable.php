<?php

namespace Goodby\EventSourcing;

interface EventNotifiable
{
    /**
     * @return void
     */
    public function notifyEvents();
}
