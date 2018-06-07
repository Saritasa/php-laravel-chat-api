<?php

namespace Saritasa\LaravelChatApi\Events;

use Saritasa\LaravelChatApi\Contracts\IChat;
use Saritasa\LaravelChatApi\Contracts\IChatUser;

/**
 * Dispatched when one of chat participants leave chat.
 */
class ChatLeavedEvent extends ChatEvent
{
    /**
     * User who leaved chat.
     *
     * @var IChatUser
     */
    public $leaver;

    /**
     * Dispatched when one of chat participants leave chat.
     *
     * @param IChat $chat Chat which user leave.
     * @param IChatUser $leaver User who leaved chat.
     */
    public function __construct(IChat $chat, IChatUser $leaver)
    {
        parent::__construct($chat->getId());
        $this->leaver = $leaver;
    }
}
