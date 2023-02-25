<?php

declare(strict_types=1);

namespace Larium\StateMachine;

class Transition
{
    public function __construct(
        private readonly string $name,
        private readonly array $initialStates,
        private readonly string $state
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getInitialStates(): array
    {
        return $this->initialStates;
    }

    public function getState(): string
    {
        return $this->state;
    }
}
