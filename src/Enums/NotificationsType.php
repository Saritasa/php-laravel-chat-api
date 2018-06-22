<?php

namespace Saritasa\LaravelChatApi\Enums;

use Saritasa\Enum;

/**
 * Available types of notifications.
 */
class NotificationsType extends Enum
{
    public const NEW_MESSAGE = 'newMessage';
    public const CHAT_CLOSED = 'chatClosed';
    public const CHAT_REOPENED = 'chatReopened';
}
