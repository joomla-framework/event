<?php
/**
 * @copyright  Copyright (C) 2005 - 2023 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Event\Tests\Stubs;

use Joomla\Event\ErrorResistibleEventInterface;
use Joomla\Event\Event;

/**
 * An error resistible event
 *
 * @since  __DEPLOY_VERSION__
 */
class ErrorResistibleEvent extends Event implements ErrorResistibleEventInterface
{
	/**
	 * @var array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $errors = [];

	/**
	 * Add an error that happened during dispatching of the event.
	 *
	 * @param   \Throwable   $error  The error instance
	 *
	 * @return void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function addError(\Throwable $error): void
	{
		$this->errors[] = $error;
	}

	/**
	 * Get list of errors that happened during dispatching of the event.
	 *
	 * @return array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}
}
