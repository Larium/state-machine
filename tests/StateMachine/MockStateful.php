<?php

declare(strict_types=1);

namespace Larium\StateMachine;

class MockStateful implements Stateful
{
    public const DRAFT = 'draft';

    public const REVIEWED = 'reviewed';

    public const CHANGE_REQUESTED = 'change requested';

    public const SUBMITTED = 'submitted';

    public const APPROVED = 'approved';

    public const DECLINED = 'declined';

    private ?string $state;

    public function __construct(string $state = null)
    {
        $this->state = $state;
    }

    public function getFiniteState(): ?string
    {
        return $this->state;
    }

    public function setFiniteState(string $state): void
    {
        $this->state = $state;
    }
}
