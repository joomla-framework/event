<?php
/**
 * Part of the Joomla Framework Event Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Event;

/**
 * Interface for event dispatchers.
 *
 * @since  1.0
 */
interface DispatcherInterface
{
	/**
	 * Attaches a listener to an event
	 *
	 * @param   string    $eventName  The event to listen to.
	 * @param   callable  $callback   A callable function.
	 * @param   integer   $priority   The priority at which the $callback executed.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function addListener($eventName, callable $callback, $priority = Priority::NORMAL);

	/**
	 * Dispatches an event to all registered listeners.
	 *
	 * @param   string          $name   The name of the event to dispatch.
	 *                                  The name of the event is the name of the method that is invoked on listeners.
	 * @param   EventInterface  $event  The event to pass to the event handlers/listeners.
	 *                                  If not supplied, an empty EventInterface instance is created.
	 *
	 * @return  EventInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function dispatch($name, EventInterface $event = null);

	/**
	 * Removes an event listener from the specified event.
	 *
	 * If no event is specified, it will be removed from all events it is listening to.
	 *
	 * @param   callable  $callback   The listener to remove.
	 * @param   string    $eventName  The event to remove a listener from.
	 *
	 * @return void
	 */
	public function removeListener(callable $listener, $eventName = null);
}
