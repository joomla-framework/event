<?php

/**
 * Part of the Joomla Framework Event Package
 *
 * @copyright  Copyright (C) 2005 - 2023 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Event;

/**
 * Interface for dispatcher with error handler.
 *
 * @since  __DEPLOY_VERSION__
 */
interface DispatcherWithErrorHandlerInterface
{
    /**
     * Set error handler for the dispatcher to handler errors in an event listeners.
     *
     * @param  ?callable   $handler  The error handler
     *
     * @return ?callable  Previous error handler
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setErrorHandler(?callable $handler): ?callable;
}
