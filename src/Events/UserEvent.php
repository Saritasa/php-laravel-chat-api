<?php

namespace Saritasa\LaravelChatApi\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

/**
 * Base class for all user chat events.
 */
abstract class UserEvent implements ShouldBroadcastNow
{
    use SerializesModels;

    /**
     * Pool chat event channel prefix.
     */
    public const CHANNEL_PREFIX = 'userChat.';

    /**
     * User identifier.
     *
     * @var string
     */
    public $userId;

    /**
     * Chat identifier.
     *
     * @var string
     */
    public $chatId;

    /**
     * Base class for all chat events.
     *
     * @param string $userId User identifier
     * @param string $chatId Chat identifier
     */
    public function __construct(string $userId, string $chatId)
    {
        $this->chatId = $chatId;
        $this->userId = $userId;
    }

    /**
     * Get the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs(): string
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
        return new PrivateChannel(static::CHANNEL_PREFIX . $this->userId);
    }
}
