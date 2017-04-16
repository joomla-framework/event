<?php
/**
 * Part of the Joomla Framework Event Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Event;

/**
 * This dispatcher can be used to avoid conditional dispatcher calls
 *
 * Dispatching should always be optional, and if no dispatcher is provided to your
 * library creating a NullDispatcher instance to have something to trigger events at
 * is a good way to avoid littering your code with `if ($this->dispatcher) { }`
 * blocks.
 *
 * @since  1.2
 */
class NullDispatcher implements DispatcherInterface
{
	/**
	 * Trigger an event.
	 *
	 * @param   EventInterface|string  $event  The event object or name.
	 *
	 * @return  EventInterface  The event after being passed through all listeners.
	 *
	 * @since   1.2
	 */
	public function triggerEvent($event)
	{
		// Do nothing
	}
}
