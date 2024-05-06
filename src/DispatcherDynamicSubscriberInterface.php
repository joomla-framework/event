<?php

/**
 * Part of the Joomla Framework Event Package
 *
 * @copyright  Copyright (C) 2005 - 2024 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Event;

/**
 * Interface for event dispatcher with support of DynamicSubscriberInterface.
 *
 * @since  __DEPLOY_VERSION__
 */
interface DispatcherDynamicSubscriberInterface
{
    /**
     * Adds an event dynamic subscriber.
     *
     * @param   DynamicSubscriberInterface  $subscriber  The subscriber.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function addDynamicSubscriber(DynamicSubscriberInterface $subscriber): void;

    /**
     * Removes an event dynamic subscriber.
     *
     * @param   DynamicSubscriberInterface  $subscriber  The subscriber.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function removeDynamicSubscriber(DynamicSubscriberInterface $subscriber): void;
}
