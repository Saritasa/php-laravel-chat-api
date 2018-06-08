<?php

namespace Saritasa\LaravelChatApi\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Default notification.
 */
class ChatNotification extends Notification
{
    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $user
     *
     * @return array
     */
    public function via($user): array
    {
        return [];
    }
}
