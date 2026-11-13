<?php
/**
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Event\Tests;

use Joomla\Event\AbstractEvent;
use Joomla\Event\Event;
use Joomla\Event\Tests\Stubs\TestAbstractEvent;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the AbstractEvent class.
 *
 * @since  1.0
 */
class AbstractEventTest extends TestCase
{
    /**
     * @testdox  The event's name can be retrieved
     *
     * @covers   Joomla\Event\AbstractEvent
     */
    public function testGetName()
    {
        $this->assertEquals('test', $this->createEventWithoutArguments()->getName());
    }

    /**
     * @testdox  A named event argument can be retrieved
     *
     * @covers   Joomla\Event\AbstractEvent
     */
    public function testGetArgument()
    {
        $event = $this->createEventWithArguments();

        $this->assertFalse($event->getArgument('non-existing', false));
        $this->assertSame('bar', $event->getArgument('string'));
        $this->assertInstanceOf(\stdClass::class, $event->getArgument('object'));
        $this->assertIsArray($event->getArgument('array'));
    }

    /**
     * @testdox  The event can be checked for a named argument
     *
     * @covers   Joomla\Event\AbstractEvent
     */
    public function testHasArgument()
    {
        $event = $this->createEventWithArguments();

        $this->assertFalse($event->hasArgument('non-existing'));
        $this->assertTrue($event->hasArgument('string'));
    }

    /**
     * @testdox  The event arguments can be retrieved
     *
     * @covers   Joomla\Event\AbstractEvent
     */
    public function testGetArguments()
    {
        $this->assertEmpty($this->createEventWithoutArguments()->getArguments());
    }

    /**
     * @testdox  The event can be checked if its propagation has been stopped
     *
     * @covers   Joomla\Event\AbstractEvent
     */
    public function testIsStopped()
    {
        $this->assertFalse($this->createEventWithoutArguments()->isStopped());
    }

    /**
     * @testdox  An event's propagation can be stopped
     *
     * @covers   Joomla\Event\AbstractEvent
     */
    public function testStopPropagation()
    {
        $event = $this->createEventWithoutArguments();

        $event->stopPropagation();
        $this->assertTrue($event->isStopped());
    }

    /**
     * @testdox  The event arguments can be counted
     *
     * @covers   Joomla\Event\AbstractEvent
     */
    public function testCount()
    {
        $this->assertCount(3, $this->createEventWithArguments());
    }

    /**
     * @testdox  The event can be serialized and unserialized
     *
     * @covers   Joomla\Event\AbstractEvent
     */
    public function testSerializeUnserialize()
    {
        $event = $this->createEventWithArguments();

        $this->assertEquals($event, unserialize(serialize($event)));
    }

    /**
     * The legacy Serializable::unserialize() rejects data that is not an array.
     */
    public function testUnserializeRejectsNonArrayData()
    {
        $this->expectException(\UnexpectedValueException::class);

        $this->createEventWithoutArguments()->unserialize(serialize(false));
    }

    /**
     * __unserialize() rejects data with missing or wrongly typed keys.
     */
    #[DataProvider('invalidSerializedDataProvider')]
    public function testUnserializeRejectsInvalidData(array $data)
    {
        $this->expectException(\UnexpectedValueException::class);

        $this->createEventWithoutArguments()->__unserialize($data);
    }

    public static function invalidSerializedDataProvider(): array
    {
        return [
            'empty'            => [[]],
            'missing stopped'  => [['name' => 'test', 'arguments' => []]],
            'null name'        => [['name' => null, 'arguments' => [], 'stopped' => false]],
            'scalar arguments' => [['name' => 'test', 'arguments' => 'foo', 'stopped' => false]],
            'non-bool stopped' => [['name' => 'test', 'arguments' => [], 'stopped' => 1]],
        ];
    }

    /**
     * @testdox  The event arguments can be checked for presence when accessing the event as an array
     *
     * @covers   Joomla\Event\AbstractEvent
     */
    public function testOffsetExists()
    {
        $event = $this->createEventWithArguments();

        $this->assertFalse(isset($event['non-existing']));
        $this->assertTrue(isset($event['string']));
    }

    /**
     * @testdox  The event arguments can be retrieved when accessing the event as an array
     *
     * @covers   Joomla\Event\AbstractEvent
     */
    public function testOffsetGet()
    {
        $event = $this->createEventWithArguments();

        $this->assertNull($event['non-existing']);
        $this->assertSame('bar', $event['string']);
        $this->assertInstanceOf(\stdClass::class, $event['object']);
        $this->assertIsArray($event['array']);
    }

    /**
     * Creates an event without any arguments
     *
     * @return  AbstractEvent|MockObject
     */
    private function createEventWithoutArguments(): AbstractEvent
    {
        return new TestAbstractEvent('test');
    }

    /**
     * Creates an event with some arguments
     *
     * @return  AbstractEvent|MockObject
     */
    private function createEventWithArguments(): AbstractEvent
    {
        return new TestAbstractEvent('test',
            [
                'string' => 'bar',
                'object' => new \stdClass(),
                'array'  => [
                    'foo'  => 'bar',
                    'test' => [
                        'foo'  => 'bar',
                        'test' => 'test',
                    ],
                ],
            ]
        );
    }
}
