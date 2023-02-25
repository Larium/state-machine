<?php

declare(strict_types=1);

namespace Larium\EventDispatcher;

use function call_user_func;
use function array_key_exists;

class EventDispatcher
{
    private array $listeners = [];

    public function addListener(string $eventName, callable $listener, int $priority = 0): self
    {
        if (!$this->eventExists($eventName)) {
            $this->listeners[$eventName][$priority] = [];
        }
        $this->listeners[$eventName][$priority][] = $listener;

        return $this;
    }

    public function dispatch(string $eventName, Event $event = null): Event
    {
        $event = $event ?: new Event();

        if ($this->eventExists($eventName)) {
            ksort($this->listeners[$eventName]);
            foreach ($this->listeners[$eventName] as $priority => $listeners) {
                foreach ($listeners as $listener) {
                    call_user_func($listener, $event, $eventName, $this);
                }
            }
        }

        return $event;
    }

    public function eventExists(string $eventName): bool
    {
        return array_key_exists($eventName, $this->listeners);
    }
}
