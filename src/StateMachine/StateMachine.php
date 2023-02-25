<?php

declare(strict_types=1);

namespace Larium\StateMachine;

use Larium\EventDispatcher\EventDispatcher;
use Larium\StateMachine\Event\StateMachineEvent;
use Larium\StateMachine\Exception\StateException;
use Larium\StateMachine\Exception\StatefulException;
use Larium\StateMachine\Event\StateMachineDispatcher;

class StateMachine
{
    private array $transitions = [];

    private array $states = [];

    private Stateful $stateful;

    private ?State $currentState;

    private StateMachineDispatcher $dispatcher;

    private bool $initialized = false;

    public function __construct(Stateful $stateful)
    {
        $this->stateful = $stateful;
        $this->dispatcher = new StateMachineDispatcher(new EventDispatcher());
    }

    public function addState(State $state): void
    {
        $this->states[$state->getName()] = $state;
    }

    public function addTransition(Transition $transition): void
    {
        $this->transitions[$transition->getName()] = $transition;

        foreach ($transition->getInitialStates() as $state) {
            try {
                $this->getState($state);
            } catch (StateException $e) {
                $this->addState(new State($state, State::TYPE_INITIAL));
            }

            $s = $this->getState($state);
            $s->addTransition($transition->getName());
        }
    }

    public function initialize(): void
    {
        if (null === $initialState = $this->stateful->getFiniteState()) {
            $states = array_filter($this->states, static function (State $item) {
                return $item->isInitial();
            });

            if (false === $state = reset($states)) {
                throw new StatefulException('Unable to find initial state');
            }

            $initialState = $state->getName();
        }

        $this->currentState = $this->getState($initialState);
        $this->initialized = true;
    }

    /**
     * @throws StateException
     */
    public function apply(string $transitionName): void
    {
        $transition = $this->getTransition($transitionName);
        $event = new StateMachineEvent($this->getCurrentState(), $transition);

        if (!$this->can($transitionName)) {
            throw new StateException(
                sprintf(
                    'The `%s` transition can not be applied to `%s` state of object',
                    $transitionName,
                    $this->getCurrentState()->getName()
                )
            );
        }

        $this->currentState = $this->getState($transition->getState());
        $this->stateful->setFiniteState($this->getCurrentState()->getName());

        $this->dispatcher->dispatch($transition->getName(), $event);
    }

    public function getDispatcher(): StateMachineDispatcher
    {
        return $this->dispatcher;
    }

    public function can(string $transitionName): bool
    {
        return in_array($transitionName, $this->getCurrentState()->getTransitions());
    }

    public function getCurrentState(): State
    {
        if ($this->initialized === false) {
            throw new StateException('You must initialize state machine before get the current state.');
        }

        return $this->currentState;
    }

    public function getState(string $name): State
    {
        if (!array_key_exists($name, $this->states)) {
            throw new StateException(
                sprintf('Unable to find a state with name `%s`', $name)
            );
        }

        return $this->states[$name];
    }

    public function getTransition(string $name): Transition
    {
        if (!array_key_exists($name, $this->transitions)) {
            throw new StateException(
                sprintf('Unable to find a transition with name `%s`', $name)
            );
        }

        return $this->transitions[$name];
    }
}
