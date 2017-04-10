<?php

namespace Saritasa\Laravel\Chat\Events;

use App\Events\Event;
use App\Model\Entities\Chat;
use App\Model\Entities\ChatMessage;
use App\Model\Entities\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChatCreated extends ChatEvent
{
    use SerializesModels;

    /**
     * @var Chat
     */
    public $chat;

    /**
     * @var User
     */
    public $fromUser;

    /**
     * NewChat constructor.
     * @param Chat $chat
     * @param User $fromUser
     */
    public function __construct(Chat $chat, User $fromUser)
    {
        $this->chat = $chat;
        $this->fromUser = $fromUser;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
