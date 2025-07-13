<?php

/**
 * Part of the Joomla Framework Event Package
 *
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Event;

/**
 * Implementation of a DispatcherInterface supporting prioritized listeners.
 *
 * @since  1.0
 */
class Dispatcher implements DispatcherInterface
{
    /**
     * An array of ListenersPriorityQueue indexed by the event names.
     *
     * @var    ListenersPriorityQueue[]
     * @since  1.0
     */
    protected $listeners = [];

    /**
     * Attaches a listener to an event
     *
     * @param   string    $eventName  The event to listen to.
     * @param   callable  $callback   A callable function
     * @param   integer   $priority   The priority at which the $callback executed
     *
     * @return  boolean
     *
     * @since   1.0
     */
    public function addListener(string $eventName, callable $callback, int $priority = 0): bool
    {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = new ListenersPriorityQueue();
        }

        $this->listeners[$eventName]->add($callback, $priority);

        return true;
    }

    /**
     * Get the priority of the given listener for the given event.
     *
     * @param   string    $eventName  The event to listen to.
     * @param   callable  $callback   A callable function
     *
     * @return  mixed  The listener priority or null if the listener doesn't exist.
     *
     * @since   1.0
     */
    public function getListenerPriority($eventName, callable $callback)
    {
        if (isset($this->listeners[$eventName])) {
            return $this->listeners[$eventName]->getPriority($callback);
        }
    }

    /**
     * Get the listeners registered to the given event.
     *
     * @param   string|null  $event  The event to fetch listeners for or null to fetch all listeners
     *
     * @return  callable[]  An array of registered listeners sorted according to their priorities.
     *
     * @since   1.0
     */
    public function getListeners(?string $event = null)
    {
        if ($event !== null) {
            if (isset($this->listeners[$event])) {
                return $this->listeners[$event]->getAll();
            }

            return [];
        }

        $dispatcherListeners = [];

        foreach ($this->listeners as $registeredEvent => $listeners) {
            $dispatcherListeners[$registeredEvent] = $listeners->getAll();
        }

        return $dispatcherListeners;
    }

    /**
     * Tell if the given listener has been added.
     *
     * If an event is specified, it will tell if the listener is registered for that event.
     *
     * @param   callable  $callback   The callable to check is listening to the event.
     * @param   ?string   $eventName  An optional event name to check a listener is subscribed to.
     *
     * @return  boolean  True if the listener is registered, false otherwise.
     *
     * @since   1.0
     */
    public function hasListener(callable $callback, ?string $eventName = null)
    {
        if ($eventName) {
            if (isset($this->listeners[$eventName])) {
                return $this->listeners[$eventName]->has($callback);
            }
        } else {
            foreach ($this->listeners as $queue) {
                if ($queue->has($callback)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Removes an event listener from the specified event.
     *
     * @param   string    $eventName  The event to remove a listener from.
     * @param   callable  $listener   The listener to remove.
     *
     * @return  void
     *
     * @since   2.0.0
     */
    public function removeListener(string $eventName, callable $listener): void
    {
        if (isset($this->listeners[$eventName])) {
            $this->listeners[$eventName]->remove($listener);
        }
    }

    /**
     * Clear the listeners in this dispatcher.
     *
     * If an event is specified, the listeners will be cleared only for that event.
     *
     * @param   string  $event  The event name.
     *
     * @return  $this
     *
     * @since   1.0
     */
    public function clearListeners($event = null)
    {
        if ($event) {
            if (isset($this->listeners[$event])) {
                unset($this->listeners[$event]);
            }
        } else {
            $this->listeners = [];
        }

        return $this;
    }

    /**
     * Count the number of registered listeners for the given event.
     *
     * @param   string  $event  The event name.
     *
     * @return  integer
     *
     * @since   1.0
     */
    public function countListeners($event)
    {
        return isset($this->listeners[$event]) ? \count($this->listeners[$event]) : 0;
    }

    /**
     * Adds an event subscriber.
     *
     * @param   SubscriberInterface  $subscriber  The subscriber.
     *
     * @return  void
     *
     * @since   2.0.0
     */
    public function addSubscriber(SubscriberInterface $subscriber): void
    {
        foreach ($subscriber->getSubscribedEvents() as $eventName => $params) {
            if (\is_array($params)) {
                $this->addListener($eventName, [$subscriber, $params[0]], $params[1] ?? Priority::NORMAL);
            } else {
                $this->addListener($eventName, [$subscriber, $params]);
            }
        }
    }

    /**
     * Removes an event subscriber.
     *
     * @param   SubscriberInterface  $subscriber  The subscriber.
     *
     * @return  void
     *
     * @since   2.0.0
     */
    public function removeSubscriber(SubscriberInterface $subscriber): void
    {
        foreach ($subscriber->getSubscribedEvents() as $eventName => $params) {
            if (\is_array($params)) {
                $this->removeListener($eventName, [$subscriber, $params[0]]);
            } else {
                $this->removeListener($eventName, [$subscriber, $params]);
            }
        }
    }

    /**
     * Dispatches an event to all registered listeners.
     *
     * @param   string          $name   The name of the event to dispatch.
     * @param   EventInterface  $event  The event to pass to the event handlers/listeners.
     *                                  If not supplied, an empty EventInterface instance is created.
     *                                  Note, not passing an event is deprecated and will be required as of 3.0.
     *
     * @return  EventInterface
     *
     * @since   2.0.0
     */
    public function dispatch(string $name, EventInterface $event): EventInterface
    {
        if (isset($this->listeners[$event->getName()])) {
            foreach ($this->listeners[$event->getName()] as $listener) {
                if ($event->isStopped()) {
                    return $event;
                }

                $listener($event);
            }
        }

        return $event;
    }
}
