<?php

/**
 * Part of the Joomla Framework Event Package
 *
 * @copyright  Copyright (C) 2005 - 2023 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Event;

/**
 * Trait with base method for ErrorResistibleEventInterface
 *
 * @since  __DEPLOY_VERSION__
 */
trait ErrorResistibleTrait
{
    /**
     * Error handler.
     *
     * @return callable
     *
     * @since  __DEPLOY_VERSION__
     */
    private $errorHandler;

    /**
     * Retrieve error handler for the event.
     *
     * @return callable
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getErrorHandler(): callable
    {
        return $this->errorHandler;
    }

    /**
     * Handle the error.
     *
     * @param   \Throwable   $error  The error instance
     *
     * @since  __DEPLOY_VERSION__
     */
    public function handleError(\Throwable $error): void
    {
        $handler = $this->getErrorHandler();
        $handler($error);
    }
}
