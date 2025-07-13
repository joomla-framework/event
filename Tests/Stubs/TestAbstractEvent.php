<?php

namespace Joomla\Event\Tests\Stubs;

use Joomla\Event\AbstractEvent;

class TestAbstractEvent extends AbstractEvent
{
    public function offsetSet(mixed $offset, mixed $value): void
    {
        // TODO: Implement offsetSet() method.
    }

    public function offsetUnset(mixed $offset): void
    {
        // TODO: Implement offsetUnset() method.
    }
}
