<?php
/**
 * @copyright  Copyright (C) 2005 - 2023 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Event\Tests\Stubs;

use Joomla\Event\ErrorResistibleEventInterface;
use Joomla\Event\ErrorResistibleTrait;
use Joomla\Event\Event;

/**
 * An error resistible event
 *
 * @since  __DEPLOY_VERSION__
 */
class ErrorResistibleEvent extends Event implements ErrorResistibleEventInterface
{
    use ErrorResistibleTrait;

    /**
     * Constructor.
     *
     * @param   string      $name          The event name.
     * @param   array       $arguments     The event arguments.
     * @param   ?callable   $errorHandler  The event arguments.
     *
     * @since   @since  __DEPLOY_VERSION__
     */
    public function __construct($name, array $arguments = [], callable $errorHandler = null)
    {
        parent::__construct($name, $arguments);

        $this->errorHandler = $errorHandler;
    }
}
