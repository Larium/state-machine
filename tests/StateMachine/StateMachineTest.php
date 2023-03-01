<?php

declare(strict_types=1);

namespace Larium\StateMachine;

use PHPUnit\Framework\TestCase;
use Larium\StateMachine\Event\StateMachineEvent;
use Larium\StateMachine\Exception\StateException;
use Larium\StateMachine\Exception\StatefulException;

class StateMachineTest extends TestCase
{
    public function testShouldAddState(): void
    {
        $s = new MockStateful(MockStateful::DRAFT);
        $sm = $this->createStateMachine($s);
        $state = $sm->getState(MockStateful::DRAFT);

        $this->assertEquals(MockStateful::DRAFT, $state->getName());
        $this->assertTrue($state->isInitial());
        $this->assertFalse($state->isNormal());
    }

    public function testShouldAddTransition(): void
    {
        $s = new MockStateful(MockStateful::DRAFT);
        $sm = $this->createStateMachine($s);

        $t1 = $sm->getTransition('t1');

        $this->assertEquals('t1', $t1->getName());
    }

    public function testShouldThrowExceptionWhenNotInitialized(): void
    {
        $this->expectException(StateException::class);
        $s = new MockStateful(MockStateful::DRAFT);
        $sm = $this->createStateMachine($s, false);

        $sm->getCurrentState();
    }

    public function testShouldThrowExceptionForUndefinedState(): void
    {
        $this->expectException(StateException::class);
        $s = new MockStateful(MockStateful::DRAFT);
        $sm = $this->createStateMachine($s);

        $sm->getState('not-existed');
    }

    public function testShouldThrowExceptionForUndefinedTransition(): void
    {
        $this->expectException(StateException::class);
        $s = new MockStateful(MockStateful::DRAFT);
        $sm = $this->createStateMachine($s);

        $sm->getTransition('not-existed');
    }

    public function testShouldSetInitialStateToStateful(): void
    {
        $s = new MockStateful();
        $sm = $this->createStateMachine($s);
        $this->assertEquals(MockStateful::DRAFT, $sm->getCurrentState()->getName());
    }

    public function testShouldThrowExceptionForWrongTransition(): void
    {
        $this->expectException(StateException::class);
        $s = new MockStateful(MockStateful::DRAFT);
        $sm = $this->createStateMachine($s);
        $sm->apply('t3');
    }

    public function testShouldStatesHaveTransitions(): void
    {
        $s = new MockStateful(MockStateful::DRAFT);
        $sm = $this->createStateMachine($s);
        $state = $sm->getState(MockStateful::DRAFT);

        $transitions = $state->getTransitions();
        $this->assertNotEmpty($transitions);
        $this->assertTrue(in_array('t1', $transitions));
    }

    public function testShouldBeAbleToCheckForCanTransitions(): void
    {
        $s = new MockStateful(MockStateful::DRAFT);
        $sm = $this->createStateMachine($s);

        $this->assertTrue($sm->can('t1'));

        $sm->apply('t1');
        $this->assertTrue($sm->can('t2'));

        $sm->apply('t2');
        $this->assertTrue($sm->can('t3'));
        $this->assertTrue($sm->can('t4'));
    }

    public function testShouldRunEvents(): void
    {
        $s = new MockStateful(MockStateful::DRAFT);
        $sm = $this->createStateMachine($s);
        $payload = null;
        $listener = function (StateMachineEvent $event, string $eventName) use (&$payload) {
            $payload = sprintf(
                'state:%s_eventName:%s_transition:%s',
                $event->getState()->getName(),
                $eventName,
                $event->getTransition()->getName()
            );
        };
        $sm->getDispatcher()->addListener('t1', $listener);
        $sm->apply('t1');

        $this->assertEquals(
            sprintf('state:%s_eventName:t1_transition:t1', MockStateful::DRAFT),
            $payload
        );
        $this->assertEquals(MockStateful::REVIEWED, $s->getFiniteState());
    }

    public function testShouldAddStatesFromTransitions(): void
    {
        $s = new MockStateful();
        $sm = new StateMachine($s);
        $this->createTransition($sm);
        $sm->initialize();

        $this->assertTrue($sm->can('t1'));
        $this->assertFalse($sm->can('t2'));
        $this->assertFalse($sm->can('t3'));

        $sm->apply('t1');
        $this->assertTrue($sm->can('t2'));

        $sm->apply('t2');
        $this->assertTrue($sm->can('t3'));
        $this->assertTrue($sm->can('t4'));
    }

    public function testShouldThrowExceptionForNotInitialState(): void
    {
        $this->expectException(StatefulException::class);
        $s = new MockStateful();
        $sm = new StateMachine($s);
        $sm->initialize();
    }

    private function createStateMachine(Stateful $stateful, $initialize = true): StateMachine
    {
        $sm = new StateMachine($stateful);

        $this->createStates($sm);
        $this->createTransition($sm);

        if ($initialize === true) {
            $sm->initialize();
        }

        return $sm;
    }

    private function createStates(StateMachine $sm): void
    {
        $sm->addState(new State(MockStateful::DRAFT, State::TYPE_INITIAL));
        $sm->addState(new State(MockStateful::REVIEWED));
        $sm->addState(new State(MockStateful::SUBMITTED));
        $sm->addState(new State(MockStateful::CHANGE_REQUESTED));
        $sm->addState(new State(MockStateful::DECLINED));
        $sm->addState(new State(MockStateful::APPROVED, State::TYPE_FINAL));
    }

    private function createTransition(StateMachine $sm): void
    {
        $sm->addTransition(new Transition('t1', [MockStateful::DRAFT], MockStateful::REVIEWED));
        $sm->addTransition(new Transition('t2', [MockStateful::REVIEWED], MockStateful::SUBMITTED));
        $sm->addTransition(new Transition('t3', [MockStateful::SUBMITTED], MockStateful::APPROVED));
        $sm->addTransition(new Transition('t4', [MockStateful::SUBMITTED], MockStateful::DECLINED));
        $sm->addTransition(new Transition('t5', [MockStateful::DECLINED], MockStateful::REVIEWED));
        $sm->addTransition(new Transition('t6', [MockStateful::REVIEWED], MockStateful::CHANGE_REQUESTED));
        $sm->addTransition(new Transition('t7', [MockStateful::CHANGE_REQUESTED], MockStateful::DRAFT));
        $sm->addTransition(new Transition('t8', [MockStateful::CHANGE_REQUESTED], MockStateful::REVIEWED));
    }
}
