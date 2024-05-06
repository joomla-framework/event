<?php
/**
 * @copyright  Copyright (C) 2005 - 2022 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Event\Tests\Stubs;

use Joomla\Event\DynamicSubscriberInterface;
use Joomla\Event\Event;
use Joomla\Event\Priority;

/**
 * A listener listening to some events.
 *
 * @since  __DEPLOY_VERSION__
 */
class SomethingDynamicListener implements DynamicSubscriberInterface
{
	/**
	 * Callback for onSomething.
	 *
	 * @var   callable
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private $onSomethingCallback;

	/**
	 * Listen to onBeforeSomething.
	 *
	 * @param   Event  $event  The event.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onBeforeSomething(Event $event)
	{
	}

	/**
	 * Listen to onAfterSomething.
	 *
	 * @param   Event  $event  The event.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onAfterSomething(Event $event)
	{
	}

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * The array keys are event names and the value can be:
	 *
	 *  - The method name to call (priority defaults to 0)
	 *  - An array composed of the method name to call and the priority
	 *
	 * For instance:
	 *
	 *  * array('eventName' => 'methodName')
	 *  * array('eventName' => array('methodName', $priority))
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getSubscribedEvents(): array
	{
		$this->onSomethingCallback = $this->onSomethingCallback ?? function (Event $event) {
		};

		return [
			'onBeforeSomething' => 'onBeforeSomething',
			'onSomething'       => $this->onSomethingCallback,
			'onAfterSomething'  => ['onAfterSomething', Priority::HIGH]
		];
	}
}
