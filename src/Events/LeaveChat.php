<?php

namespace Saritasa\LaravelChatApi\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Saritasa\Laravel\Chat\Contracts\IChat;
use Saritasa\Laravel\Chat\Contracts\IChatUser;

class LeaveChat extends ChatEvent
{
    public $leaver;

    public function __construct(IChatUser $leaver, IChat $chat)
    {
        parent::__construct($chat->getId());
        $this->leaver = $leaver;
    }
}
