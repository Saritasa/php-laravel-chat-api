<?php

namespace Saritasa\LaravelChatApi\Events;

use Saritasa\LaravelChatApi\Contracts\IChat;
use Saritasa\LaravelChatApi\Contracts\IChatUser;

class LeaveChat extends ChatEvent
{
    public $leaver;

    public function __construct(IChat $chat, IChatUser $leaver)
    {
        parent::__construct($chat->getId());
        $this->leaver = $leaver;
    }
}
