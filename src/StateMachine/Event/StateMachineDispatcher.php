<?php

declare(strict_types=1);

namespace Larium\StateMachine\Event;

use Larium\EventDispatcher\EventDispatcher;

class StateMachineDispatcher
{
    public function __construct(
        private readonly EventDispatcher $eventDispatcher
    ) {
    }

    public function dispatch(string $eventName, StateMachineEvent $event): void
    {
        $this->eventDispatcher->dispatch($eventName, $event);
    }

    public function addListener(string $eventName, callable $listener, $priority = 0): void
    {
        $this->eventDispatcher->addListener($eventName, $listener, $priority);
    }
}
