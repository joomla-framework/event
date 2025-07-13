<?php
/**
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Event\Tests;

use Joomla\Event\Dispatcher;
use Joomla\Event\Event;
use Joomla\Event\EventImmutable;
use Joomla\Event\EventInterface;
use Joomla\Event\Priority;
use Joomla\Event\Tests\Stubs\FirstListener;
use Joomla\Event\Tests\Stubs\SecondListener;
use Joomla\Event\Tests\Stubs\SomethingListener;
use Joomla\Event\Tests\Stubs\ThirdListener;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Dispatcher class.
 */
class DispatcherTest extends TestCase
{
    /**
     * Object being tested
     *
     * @var  Dispatcher
     */
    private $instance;

    /**
     * Sets up the fixture.
     *
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->instance = new Dispatcher();
    }

    /**
     * @testdox  Event listeners can be added to the dispatcher
     *
     * @covers   Joomla\Event\Dispatcher
     * @uses     Joomla\Event\ListenersPriorityQueue
     */
    public function testAddListener()
    {
        // Add 3 listeners listening to the same events.
        $listener1 = new SomethingListener();
        $listener2 = new SomethingListener();
        $listener3 = new SomethingListener();

        $this->instance->addListener('onBeforeSomething', [$listener1, 'onBeforeSomething']);
        $this->instance->addListener('onSomething', [$listener1, 'onSomething']);
        $this->instance->addListener('onAfterSomething', [$listener1, 'onAfterSomething']);
        $this->instance->addListener('onBeforeSomething', [$listener2, 'onBeforeSomething']);
        $this->instance->addListener('onSomething', [$listener2, 'onSomething']);
        $this->instance->addListener('onAfterSomething', [$listener2, 'onAfterSomething']);
        $this->instance->addListener('onBeforeSomething', [$listener3, 'onBeforeSomething']);
        $this->instance->addListener('onSomething', [$listener3, 'onSomething']);
        $this->instance->addListener('onAfterSomething', [$listener3, 'onAfterSomething']);

        $this->assertTrue($this->instance->hasListener([$listener1, 'onBeforeSomething']));
        $this->assertTrue($this->instance->hasListener([$listener1, 'onSomething']));
        $this->assertTrue($this->instance->hasListener([$listener1, 'onAfterSomething']));

        $this->assertTrue($this->instance->hasListener([$listener2, 'onBeforeSomething']));
        $this->assertTrue($this->instance->hasListener([$listener2, 'onSomething']));
        $this->assertTrue($this->instance->hasListener([$listener2, 'onAfterSomething']));

        $this->assertTrue($this->instance->hasListener([$listener3, 'onBeforeSomething']));
        $this->assertTrue($this->instance->hasListener([$listener3, 'onSomething']));
        $this->assertTrue($this->instance->hasListener([$listener3, 'onAfterSomething']));

        $this->assertEquals(Priority::NORMAL, $this->instance->getListenerPriority('onBeforeSomething', [$listener1, 'onBeforeSomething']));
        $this->assertEquals(Priority::NORMAL, $this->instance->getListenerPriority('onSomething', [$listener1, 'onSomething']));
        $this->assertEquals(Priority::NORMAL, $this->instance->getListenerPriority('onAfterSomething', [$listener1, 'onAfterSomething']));

        $this->assertEquals(Priority::NORMAL, $this->instance->getListenerPriority('onBeforeSomething', [$listener2, 'onBeforeSomething']));
        $this->assertEquals(Priority::NORMAL, $this->instance->getListenerPriority('onSomething', [$listener2, 'onSomething']));
        $this->assertEquals(Priority::NORMAL, $this->instance->getListenerPriority('onAfterSomething', [$listener2, 'onAfterSomething']));

        $this->assertEquals(Priority::NORMAL, $this->instance->getListenerPriority('onBeforeSomething', [$listener3, 'onBeforeSomething']));
        $this->assertEquals(Priority::NORMAL, $this->instance->getListenerPriority('onSomething', [$listener3, 'onSomething']));
        $this->assertEquals(Priority::NORMAL, $this->instance->getListenerPriority('onAfterSomething', [$listener3, 'onAfterSomething']));
    }

    /**
     * @testdox  Event listeners can be added to the dispatcher with specified priorities
     *
     * @covers   Joomla\Event\Dispatcher
     * @uses     Joomla\Event\ListenersPriorityQueue
     */
    public function testAddListenerSpecifiedPriorities()
    {
        $listener = new SomethingListener();

        $this->instance->addListener('onBeforeSomething', [$listener, 'onBeforeSomething'], Priority::MIN);
        $this->instance->addListener('onSomething', [$listener, 'onSomething'], Priority::ABOVE_NORMAL);
        $this->instance->addListener('onAfterSomething', [$listener, 'onAfterSomething'], Priority::MAX);

        $this->assertTrue($this->instance->hasListener([$listener, 'onBeforeSomething']));
        $this->assertTrue($this->instance->hasListener([$listener, 'onSomething']));
        $this->assertTrue($this->instance->hasListener([$listener, 'onAfterSomething']));

        $this->assertEquals(Priority::MIN, $this->instance->getListenerPriority('onBeforeSomething', [$listener, 'onBeforeSomething']));
        $this->assertEquals(Priority::ABOVE_NORMAL, $this->instance->getListenerPriority('onSomething', [$listener, 'onSomething']));
        $this->assertEquals(Priority::MAX, $this->instance->getListenerPriority('onAfterSomething', [$listener, 'onAfterSomething']));
    }

    /**
     * @testdox  Event listeners can be added to the dispatcher as Closures
     *
     * @covers   Joomla\Event\Dispatcher
     * @uses     Joomla\Event\ListenersPriorityQueue
     */
    public function testAddClosureListener()
    {
        $listener = static function (EventInterface $event) {

        };

        $this->instance->addListener('onSomething', $listener, Priority::HIGH);
        $this->instance->addListener('onAfterSomething', $listener, Priority::NORMAL);

        $this->assertTrue($this->instance->hasListener($listener, 'onSomething'));
        $this->assertTrue($this->instance->hasListener($listener, 'onAfterSomething'));

        $this->assertEquals(Priority::HIGH, $this->instance->getListenerPriority('onSomething', $listener));
        $this->assertEquals(Priority::NORMAL, $this->instance->getListenerPriority('onAfterSomething', $listener));
    }

    /**
     * @testdox  The priority for an event listener can be retrieved
     *
     * @covers   Joomla\Event\Dispatcher
     * @uses     Joomla\Event\ListenersPriorityQueue
     */
    public function testGetListenerPriority()
    {
        $listener = new SomethingListener();
        $this->instance->addListener('onSomething', [$listener, 'onSomething']);

        $this->assertEquals(
            Priority::NORMAL,
            $this->instance->getListenerPriority(
                'onSomething',
                [$listener, 'onSomething']
            )
        );
    }

    /**
     * @testdox  The event listeners can be retrieved from the dispatcher
     *
     * @covers   Joomla\Event\Dispatcher
     * @uses     Joomla\Event\ListenersPriorityQueue
     */
    public function testGetListeners()
    {
        $this->assertEmpty($this->instance->getListeners('onSomething'));

        // Add 3 listeners listening to the same events.
        $listener1 = new SomethingListener();
        $listener2 = new SomethingListener();
        $listener3 = new SomethingListener();

        $this->instance->addListener('onBeforeSomething', [$listener1, 'onBeforeSomething']);
        $this->instance->addListener('onSomething', [$listener1, 'onSomething']);
        $this->instance->addListener('onAfterSomething', [$listener1, 'onAfterSomething']);
        $this->instance->addListener('onBeforeSomething', [$listener2, 'onBeforeSomething']);
        $this->instance->addListener('onSomething', [$listener2, 'onSomething']);
        $this->instance->addListener('onAfterSomething', [$listener2, 'onAfterSomething']);
        $this->instance->addListener('onBeforeSomething', [$listener3, 'onBeforeSomething']);
        $this->instance->addListener('onSomething', [$listener3, 'onSomething']);
        $this->instance->addListener('onAfterSomething', [$listener3, 'onAfterSomething']);

        $allListeners = [
            'onBeforeSomething' => [
                [$listener1, 'onBeforeSomething'],
                [$listener2, 'onBeforeSomething'],
                [$listener3, 'onBeforeSomething'],
            ],
            'onSomething'       => [
                [$listener1, 'onSomething'],
                [$listener2, 'onSomething'],
                [$listener3, 'onSomething'],
            ],
            'onAfterSomething'  => [
                [$listener1, 'onAfterSomething'],
                [$listener2, 'onAfterSomething'],
                [$listener3, 'onAfterSomething'],
            ],
        ];

        $onBeforeSomethingListeners = $this->instance->getListeners('onBeforeSomething');

        $this->assertSame($allListeners['onBeforeSomething'], $this->instance->getListeners('onBeforeSomething'));
        $this->assertSame($allListeners['onSomething'], $this->instance->getListeners('onSomething'));
        $this->assertSame($allListeners['onAfterSomething'], $this->instance->getListeners('onAfterSomething'));
        $this->assertSame($allListeners, $this->instance->getListeners());
    }

    /**
     * @testdox  The dispatcher can be checked to determine if a listener is registered
     *
     * @covers   Joomla\Event\Dispatcher
     * @uses     Joomla\Event\ListenersPriorityQueue
     */
    public function testHasListener()
    {
        $listener = new SomethingListener();
        $this->instance->addListener('onSomething', [$listener, 'onSomething']);
        $this->assertTrue($this->instance->hasListener([$listener, 'onSomething'], 'onSomething'));
    }

    /**
     * @testdox  Event listeners can be removed from the dispatcher
     *
     * @covers   Joomla\Event\Dispatcher
     * @uses     Joomla\Event\ListenersPriorityQueue
     */
    public function testRemoveListeners()
    {
        // Add 3 listeners listening to the same events.
        $listener1 = new SomethingListener();
        $listener2 = new SomethingListener();
        $listener3 = new SomethingListener();

        $this->instance->addListener('onBeforeSomething', [$listener1, 'onBeforeSomething']);
        $this->instance->addListener('onBeforeSomething', [$listener2, 'onBeforeSomething']);
        $this->instance->addListener('onBeforeSomething', [$listener3, 'onBeforeSomething']);

        // Remove the listener from a specific event.
        $this->instance->removeListener('onBeforeSomething', [$listener1, 'onBeforeSomething']);

        $this->assertFalse($this->instance->hasListener([$listener1, 'onBeforeSomething']));
        $this->assertTrue($this->instance->hasListener([$listener2, 'onBeforeSomething']));
        $this->assertTrue($this->instance->hasListener([$listener3, 'onBeforeSomething']));
    }

    /**
     * @testdox  The event listeners can be cleared from the dispatcher
     *
     * @covers   Joomla\Event\Dispatcher
     * @uses     Joomla\Event\ListenersPriorityQueue
     */
    public function testClearListeners()
    {
        // Add 3 listeners listening to the same events.
        $listener1 = new SomethingListener();
        $listener2 = new SomethingListener();
        $listener3 = new SomethingListener();

        $this->instance->addListener('onBeforeSomething', [$listener1, 'onBeforeSomething']);
        $this->instance->addListener('onSomething', [$listener1, 'onSomething']);
        $this->instance->addListener('onAfterSomething', [$listener1, 'onAfterSomething']);
        $this->instance->addListener('onBeforeSomething', [$listener2, 'onBeforeSomething']);
        $this->instance->addListener('onSomething', [$listener2, 'onSomething']);
        $this->instance->addListener('onAfterSomething', [$listener2, 'onAfterSomething']);
        $this->instance->addListener('onBeforeSomething', [$listener3, 'onBeforeSomething']);
        $this->instance->addListener('onSomething', [$listener3, 'onSomething']);
        $this->instance->addListener('onAfterSomething', [$listener3, 'onAfterSomething']);

        // Test without specified event.
        $this->instance->clearListeners();

        $this->assertFalse($this->instance->hasListener([$listener1, 'onBeforeSomething']));
        $this->assertFalse($this->instance->hasListener([$listener2, 'onSomething']));
        $this->assertFalse($this->instance->hasListener([$listener3, 'onAfterSomething']));

        // Test with an event specified.

        $this->instance->addListener('onBeforeSomething', [$listener1, 'onBeforeSomething']);
        $this->instance->addListener('onSomething', [$listener1, 'onSomething']);
        $this->instance->addListener('onAfterSomething', [$listener1, 'onAfterSomething']);
        $this->instance->addListener('onBeforeSomething', [$listener2, 'onBeforeSomething']);
        $this->instance->addListener('onSomething', [$listener2, 'onSomething']);
        $this->instance->addListener('onAfterSomething', [$listener2, 'onAfterSomething']);
        $this->instance->addListener('onBeforeSomething', [$listener3, 'onBeforeSomething']);
        $this->instance->addListener('onSomething', [$listener3, 'onSomething']);
        $this->instance->addListener('onAfterSomething', [$listener3, 'onAfterSomething']);

        $this->instance->clearListeners('onSomething');

        $this->assertTrue($this->instance->hasListener([$listener1, 'onBeforeSomething']));
        $this->assertFalse($this->instance->hasListener([$listener2, 'onSomething']));
        $this->assertTrue($this->instance->hasListener([$listener3, 'onAfterSomething']));

        $this->assertFalse($this->instance->hasListener([$listener1, 'onSomething']));
        $this->assertFalse($this->instance->hasListener([$listener3, 'onSomething']));
    }

    /**
     * @testdox  Event listeners can be counted
     *
     * @covers   Joomla\Event\Dispatcher
     * @uses     Joomla\Event\ListenersPriorityQueue
     */
    public function testCountListeners()
    {
        $this->assertEquals(0, $this->instance->countListeners('onTest'));

        // Add 3 listeners listening to the same events.
        $listener1 = new SomethingListener();
        $listener2 = new SomethingListener();
        $listener3 = new SomethingListener();

        $this->instance->addListener('onBeforeSomething', [$listener1, 'onBeforeSomething']);
        $this->instance->addListener('onSomething', [$listener1, 'onSomething']);
        $this->instance->addListener('onAfterSomething', [$listener1, 'onAfterSomething']);
        $this->instance->addListener('onBeforeSomething', [$listener2, 'onBeforeSomething']);
        $this->instance->addListener('onSomething', [$listener2, 'onSomething']);
        $this->instance->addListener('onAfterSomething', [$listener2, 'onAfterSomething']);
        $this->instance->addListener('onBeforeSomething', [$listener3, 'onBeforeSomething']);
        $this->instance->addListener('onSomething', [$listener3, 'onSomething']);
        $this->instance->addListener('onAfterSomething', [$listener3, 'onAfterSomething']);

        $this->assertEquals(3, $this->instance->countListeners('onSomething'));
    }

    /**
     * @testdox  An event subscriber is registered to the dispatcher
     *
     * @covers   Joomla\Event\Dispatcher
     * @uses     Joomla\Event\ListenersPriorityQueue
     */
    public function testAddSubscriber()
    {
        $listener = new SomethingListener();

        // Add our event subscriber
        $this->instance->addSubscriber($listener);

        $this->assertTrue($this->instance->hasListener([$listener, 'onBeforeSomething']));
        $this->assertTrue($this->instance->hasListener([$listener, 'onSomething']));
        $this->assertTrue($this->instance->hasListener([$listener, 'onAfterSomething']));

        $this->assertEquals(Priority::NORMAL, $this->instance->getListenerPriority('onBeforeSomething', [$listener, 'onBeforeSomething']));
        $this->assertEquals(Priority::NORMAL, $this->instance->getListenerPriority('onSomething', [$listener, 'onSomething']));
        $this->assertEquals(Priority::HIGH, $this->instance->getListenerPriority('onAfterSomething', [$listener, 'onAfterSomething']));
    }

    /**
     * @testdox  An event subscriber is removed from the dispatcher
     *
     * @covers   Joomla\Event\Dispatcher
     * @uses     Joomla\Event\ListenersPriorityQueue
     */
    public function testRemoveSubscriber()
    {
        $listener = new SomethingListener();

        // Add our event subscriber
        $this->instance->addSubscriber($listener);

        // And now remove it
        $this->instance->removeSubscriber($listener);

        $this->assertFalse($this->instance->hasListener([$listener, 'onBeforeSomething']));
        $this->assertFalse($this->instance->hasListener([$listener, 'onSomething']));
        $this->assertFalse($this->instance->hasListener([$listener, 'onAfterSomething']));
    }
}
