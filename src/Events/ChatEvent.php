<?php

namespace Saritasa\Laravel\Chat\Events;

/**
 * This is base class for all events
 *
 * @package App\Events
 */
abstract class ChatEvent
{
    /** Pool chat event channel prefix */
    const EVENT_MESSAGE_CREATED = 'CHAT-';

    /** Push notification events channel prefix */
    const EVENT_NOTIFICATION = 'NotificationPush';

    /**
     * Get the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        $params = explode('\\', get_called_class());

        return end($params);
    }
}
