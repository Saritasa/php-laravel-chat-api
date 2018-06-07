<?php

namespace Saritasa\LaravelChatApi\Events;

use Saritasa\LaravelChatApi\Contracts\IChat;
use Saritasa\LaravelChatApi\Contracts\IChatUser;

class ChatCreated extends ChatEvent
{
    public $chatParticipant;

    public function __construct(IChat $chat, IChatUser $chatUser)
    {
        parent::__construct($chat->getId());
        $this->chatParticipant = $chatUser;
    }
}
