<?php

declare(strict_types=1);

namespace Larium\StateMachine\Event;

use Larium\StateMachine\State;
use Larium\EventDispatcher\Event;
use Larium\StateMachine\Transition;

class StateMachineEvent extends Event
{
    public function __construct(
        private readonly State $state,
        private readonly Transition $transition
    ) {
    }

    public function getState(): State
    {
        return $this->state;
    }

    public function getTransition(): Transition
    {
        return $this->transition;
    }
}
