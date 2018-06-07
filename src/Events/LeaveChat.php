<?php

namespace Saritasa\LaravelChatApi\Events;

use Saritasa\Laravel\Chat\Contracts\IChat;
use Saritasa\Laravel\Chat\Contracts\IChatUser;

class LeaveChat extends ChatEvent
{
    public $leaver;

    public function __construct(IChat $chat, IChatUser $leaver)
    {
        parent::__construct($chat->getId());
        $this->leaver = $leaver;
    }
}
