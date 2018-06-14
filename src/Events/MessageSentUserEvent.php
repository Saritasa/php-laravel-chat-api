<?php

namespace Saritasa\LaravelChatApi\Events;

use Saritasa\LaravelChatApi\Contracts\IChat;
use Saritasa\LaravelChatApi\Contracts\IChatMessage;
use Saritasa\LaravelChatApi\Contracts\IChatUser;

/**
 * Dispatched when new message is posted to pool chat.
 */
class MessageSentUserEvent extends UserEvent
{
    /**
     * Message.
     *
     * @var string
     */
    public $message;

    /**
     * User which send message.
     *
     * @var IChatUser
     */
    public $sender;

    /**
     * Dispatched when new message is posted to pool chat.
     *
     * @param IChatUser $chatUser User which should receive event
     * @param IChat $chat Chat in which message was sent
     * @param IChatUser $sender User which send message
     * @param IChatMessage $chatMessage Message
     */
    public function __construct(IChatUser $chatUser, IChat $chat, IChatUser $sender, IChatMessage $chatMessage)
    {
        parent::__construct($chatUser->getId(), $chat->getId());
        $this->message = $chatMessage->getMessage();
        $this->sender = $sender;
    }
}
