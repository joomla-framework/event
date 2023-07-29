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
 * Event implementing this interface allows to handle errors of the event listener.
 *
 * @since  __DEPLOY_VERSION__
 */
interface ErrorResistibleEventInterface
{
    /**
     * Retrieve error handler for the event.
     *
     * @return callable
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getErrorHandler(): callable;

    /**
     * Handle the error.
     *
     * @param   \Throwable   $error  The error instance
     *
     * @since  __DEPLOY_VERSION__
     */
    public function handleError(\Throwable $error): void;
}
