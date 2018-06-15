<?php

namespace Saritasa\LaravelChatApi\Notifications;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Notifications\Notification;
use Saritasa\LaravelChatApi\Contracts\INotificationsFactory;
use Saritasa\LaravelChatApi\Enums\NotificationsType;

/**
 * {@inheritdoc}
 */
class NotificationsFactory implements INotificationsFactory
{
    /**
     * Di container.
     *
     * @var Application
     */
    protected $application;

    /**
     * Notifications factory.
     *
     * @param Application $application Di container
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * {@inheritdoc}
     */
    public function build(string $type): Notification
    {
        switch ($type) {
            case NotificationsType::NEW_MESSAGE:
                return $this->application->make(config('laravel_chat_api.notifications.newMessage'));
            case NotificationsType::CHAT_CLOSED:
                return $this->application->make(config('laravel_chat_api.notifications.chatClosed'));
            default:
                return $this->application->make(config('laravel_chat_api.notifications.newMessage'));
        }
    }
}
