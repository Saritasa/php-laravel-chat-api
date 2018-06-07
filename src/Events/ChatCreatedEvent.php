<?php

namespace Saritasa\LaravelChatApi\Events;

use Saritasa\LaravelChatApi\Contracts\IChat;
use Saritasa\LaravelChatApi\Contracts\IChatUser;

/**
 * Dispatched when chat created.
 */
class ChatCreatedEvent extends ChatEvent
{
    /**
     * Participant of chat which need to be notified.
     *
     * @var IChatUser
     */
    public $chatParticipant;

    /**
     * Dispatched when chat created.
     *
     * @param IChat $chat Chat which user leave
     * @param IChatUser $chatUser Participant of chat which need to be notified
     */
    public function __construct(IChat $chat, IChatUser $chatUser)
    {
        parent::__construct($chat->getId());
        $this->chatParticipant = $chatUser;
    }
}
