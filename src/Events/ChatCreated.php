<?php

namespace Saritasa\LaravelChatApi\Events;

use Saritasa\Laravel\Chat\Contracts\IChat;
use Saritasa\Laravel\Chat\Contracts\IChatParticipant;
use Saritasa\Laravel\Chat\Contracts\IChatUser;

class ChatCreated extends ChatEvent
{
    public $chatParticipant;

    public function __construct(IChat $chat, IChatParticipant $chatParticipant)
    {
        parent::__construct($chat->getId());
        $this->chatParticipant = $chatParticipant;
    }
}
