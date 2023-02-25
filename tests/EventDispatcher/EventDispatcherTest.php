<?php

declare(strict_types=1);

namespace Larium\EventDispatcher;

use PHPUnit\Framework\TestCase;

class EventDispatcherTest extends TestCase
{
    public int $counter = 0;

    public function testAddListeners(): void
    {
        $d = new EventDispatcher();
        $d->addListener('CREATED', function (Event $event, string $evenName, EventDispatcher $dispatcher) {
            self::assertEquals('CREATED', $evenName);
        }, 1);

        $d->dispatch('CREATED');
    }

    public function testAddListenersWithPriority(): void
    {
        $d = new EventDispatcher();
        $d->addListener('CREATED', function (Event $event, string $evenName, EventDispatcher $dispatcher) {
            $this->assertEquals(0, $this->counter);
            $this->counter++;
        }, 0)->addListener('CREATED', function () {
            $this->assertEquals(1, $this->counter);
            $this->counter++;
        }, 1)->addListener('CREATED', function () {
            $this->assertEquals(3, $this->counter);
            $this->counter++;
        }, 3)->addListener('CREATED', function () {
            $this->assertEquals(2, $this->counter);
            $this->counter++;
        }, 2);

        $this->counter = 0;
        $d->dispatch('CREATED');
    }

    public function testAddListenersWithAutoPriority(): void
    {
        $d = new EventDispatcher();
        $d->addListener('CREATED', function (Event $event, string $evenName, EventDispatcher $dispatcher) {
            $this->assertEquals(0, $this->counter);
            $this->counter++;
        })->addListener('CREATED', function () {
            $this->assertEquals(1, $this->counter);
            $this->counter++;
        })->addListener('CREATED', function () {
            $this->assertEquals(2, $this->counter);
            $this->counter++;
        })->addListener('CREATED', function () {
            $this->assertEquals(3, $this->counter);
            $this->counter++;
        });

        $this->counter = 0;
        $d->dispatch('CREATED');
    }

    public function testAddListenersWithPriorityDifferentEvents(): void
    {
        $d = new EventDispatcher();
        $d->addListener('CREATED', function (Event $event, string $evenName, EventDispatcher $dispatcher) {
            $this->assertEquals(0, $this->counter);
            $this->counter++;
        }, 0)->addListener('CREATED', function () {
            $this->assertEquals(1, $this->counter);
            $this->counter++;
        }, 1)->addListener('UPDATED', function () {
            $this->assertEquals(0, $this->counter);
            $this->counter++;
        }, 0)->addListener('UPDATED', function () {
            $this->assertEquals(1, $this->counter);
            $this->counter++;
        }, 1);

        $this->counter = 0;
        $d->dispatch('CREATED');
        $this->counter = 0;
        $d->dispatch('UPDATED');
    }

    public function testNoListenerShouldBeTriggered(): void
    {
        $d = new EventDispatcher();
        $d->addListener('CREATED', function (Event $event, string $evenName, EventDispatcher $dispatcher) {
            $this->counter++;
        });

        $this->counter = 0;
        $d->dispatch('UPDATED');

        $this->assertEquals(0, $this->counter);
    }
}
