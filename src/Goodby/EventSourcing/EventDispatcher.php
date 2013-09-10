<?php

namespace Goodby\EventSourcing;

interface EventDispatcher
{
    /**
     * @param DispatchableEvent $dispatchableEvent
     * @return void
     */
    public function dispatch(DispatchableEvent $dispatchableEvent);

    /**
     * @param EventDispatcher $eventDispatcher
     * @return void
     */
    public function registerEventDispatcher(EventDispatcher $eventDispatcher);

    /**
     * @param DispatchableEvent $dispatchableEvent
     * @return bool
     */
    public function understands(DispatchableEvent $dispatchableEvent);
}
