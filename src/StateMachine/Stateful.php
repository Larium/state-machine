<?php

declare(strict_types=1);

namespace Larium\StateMachine;

interface Stateful
{
    public function getFiniteState(): ?string;

    public function setFiniteState(string $state): void;
}
