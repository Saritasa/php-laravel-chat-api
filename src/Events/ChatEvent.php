<?php

namespace Saritasa\LaravelChatApi\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Saritasa\Laravel\Chat\Contracts\IChat;

/**
 * This is base class for all events
 *
 * @package App\Events
 */
abstract class ChatEvent implements ShouldBroadcastNow
{
    use SerializesModels;

    /** Pool chat event channel prefix */
    public const CHANNEL_PREFIX = 'CHAT-';

    /** Push notification events channel prefix */
    public const EVENT_NOTIFICATION = 'NotificationPush';

    /**
     * Chat
     *
     * @var IChat
     */
    public $chatId;

    public function __construct(string $chatId)
    {
        $this->chatId = $chatId;
    }

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

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return PrivateChannel
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(static::CHANNEL_PREFIX . $this->chatId);
    }
}
