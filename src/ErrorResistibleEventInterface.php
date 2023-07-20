<?php

/**
 * Part of the Joomla Framework Event Package
 *
 * @copyright  Copyright (C) 2005 - 2023 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Event;

/**
 * Interface for error resistible events.
 * Event do not crash the dispatching process when an exception happening,
 * the exception is collected, and the dispatching process is continued.
 *
 * @since  __DEPLOY_VERSION__
 */
interface ErrorResistibleEventInterface
{
    /**
     * Add an error that happened during dispatching of the event.
     *
     * @param   \Throwable $error The error instance
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function addError(\Throwable $error): void;

    /**
     * Get list of errors that happened during dispatching of the event.
     *
     * @return array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getErrors(): array;
}
