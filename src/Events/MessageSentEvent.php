<?php

namespace Saritasa\LaravelChatApi\Events;

use Saritasa\LaravelChatApi\Contracts\IChat;
use Saritasa\LaravelChatApi\Contracts\IChatMessage;
use Saritasa\LaravelChatApi\Contracts\IChatUser;

/**
 * Dispatched when new message is posted to pool chat.
 */
class MessageSentEvent extends ChatEvent
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
     * @param IChat $chat Chat in which message was sent
     * @param IChatUser $sender User which send message
     * @param IChatMessage $chatMessage Message
     */
    public function __construct(IChat $chat, IChatUser $sender, IChatMessage $chatMessage)
    {
        parent::__construct($chat->getId());
        $this->message = $chatMessage->getMessage();
        $this->sender = $sender;
    }
}
