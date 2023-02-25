<?php

declare(strict_types=1);

namespace Larium\StateMachine;

class State
{
    public const TYPE_INITIAL = 'initial';

    public const TYPE_FINAL = 'final';

    public const TYPE_NORMAL = 'normal';

    private array $transitions = [];

    public function __construct(
        private readonly string $name,
        private readonly string $type = self::TYPE_NORMAL
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isInitial(): bool
    {
        return $this->type === self::TYPE_INITIAL;
    }

    public function isNormal(): bool
    {
        return $this->type === self::TYPE_NORMAL;
    }

    public function addTransition(string $transitionName): void
    {
        $this->transitions[] = $transitionName;
    }

    public function getTransitions(): array
    {
        return $this->transitions;
    }
}
