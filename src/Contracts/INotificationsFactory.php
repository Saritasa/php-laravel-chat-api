<?php

namespace Saritasa\LaravelChatApi\Contracts;

use Illuminate\Notifications\Notification;

/**
 * Notifications factory.
 */
interface INotificationsFactory
{
    /**
     * Get notification instance by class.
     *
     * @param string $type Type of notification
     *
     * @return Notification
     */
    public function build(string $type): Notification;
}
