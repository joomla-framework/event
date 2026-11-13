<?php

/**
 * Part of the Joomla Framework Event Package
 *
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Event;

use ArrayAccess;
use Countable;

/**
 * Implementation of EventInterface.
 *
 * @since  1.0
 */
abstract class AbstractEvent implements EventInterface, ArrayAccess, Countable
{
    /**
     * The event name.
     *
     * @var    string
     * @since  1.0
     */
    protected string $name;

    /**
     * The event arguments.
     *
     * @var    array
     * @since  1.0
     */
    protected array $arguments;

    /**
     * A flag to see if the event propagation is stopped.
     *
     * @var    boolean
     * @since  1.0
     */
    protected bool $stopped = false;

    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @since   1.0
     */
    public function __construct(string $name, array $arguments = [])
    {
        $this->name      = $name;
        $this->arguments = $arguments;
    }

    /**
     * Get the event name.
     *
     * @return  string  The event name.
     *
     * @since   1.0
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get an event argument value.
     *
     * @param   string  $name     The argument name.
     * @param   mixed   $default  The default value if not found.
     *
     * @return  mixed  The argument value or the default value.
     *
     * @since   1.0
     */
    public function getArgument(string|int $name, mixed $default = null): mixed
    {
        if (isset($this->arguments[$name])) {
            return $this->arguments[$name];
        }

        return $default;
    }

    /**
     * Tell if the given event argument exists.
     *
     * @param   string  $name  The argument name.
     *
     * @return  boolean  True if it exists, false otherwise.
     *
     * @since   1.0
     */
    public function hasArgument(string|int $name): bool
    {
        return isset($this->arguments[$name]);
    }

    /**
     * Get all event arguments.
     *
     * @return  array  An associative array of argument names as keys and their values as values.
     *
     * @since   1.0
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Tell if the event propagation is stopped.
     *
     * @return  boolean  True if stopped, false otherwise.
     *
     * @since   1.0
     */
    public function isStopped(): bool
    {
        return $this->stopped === true;
    }

    /**
     * Stops the propagation of the event to further event listeners.
     *
     * @return  void
     *
     * @since   2.0.0
     */
    public function stopPropagation(): void
    {
        $this->stopped = true;
    }

    /**
     * Count the number of arguments.
     *
     * @return  integer  The number of arguments.
     *
     * @since   1.0
     */
    public function count(): int
    {
        return \count($this->arguments);
    }

    /**
     * Serialize the event.
     *
     * @return  array  The data to be serialized
     *
     * @since   2.0.0
     */
    public function __serialize(): array
    {
        return [
            'name'      => $this->name,
            'arguments' => $this->arguments,
            'stopped'   => $this->stopped,
        ];
    }

    /**
     * Unserialize the event.
     *
     * @param   array  $data  The serialized event.
     *
     * @return  void
     *
     * @since   2.0.0
     */
    public function __unserialize(array $data): void
    {
        $this->name      = $data['name'];
        $this->arguments = $data['arguments'];
        $this->stopped   = $data['stopped'];
    }

    /**
     * Tell if the given event argument exists.
     *
     * @param   string  $name  The argument name.
     *
     * @return  boolean  True if it exists, false otherwise.
     *
     * @since   1.0
     */
    public function offsetExists(mixed $name): bool
    {
        return $this->hasArgument($name);
    }

    /**
     * Get an event argument value.
     *
     * @param   string  $name  The argument name.
     *
     * @return  mixed  The argument value or null if not existing.
     *
     * @since   1.0
     */
    public function offsetGet(mixed $name): mixed
    {
        return $this->getArgument($name);
    }
}
