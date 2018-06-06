<?php

namespace Saritasa\LaravelChatApi\Events;

use Saritasa\Laravel\Chat\Contracts\IChat;
use Saritasa\Laravel\Chat\Contracts\IChatMessage;
use Saritasa\Laravel\Chat\Contracts\IChatUser;

/**
 * Event fires, when new message is posted to pool chat.
 */
class MessageSent extends ChatEvent
{
    public $message;
    public $sender;

    public function __construct(IChat $chat, IChatUser $sender, IChatMessage $chatMessage)
    {
        parent::__construct($chat->getId());
        $this->message = $chatMessage->getMessage();
        $this->sender = $sender;
    }
}
