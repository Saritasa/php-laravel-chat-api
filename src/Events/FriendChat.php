<?php

namespace Saritasa\Laravel\Chat\Events;

use App\Events\Event;
use App\Model\Entities\ChatMessage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class FriendChat extends ChatEvent
{
    use SerializesModels;

    /**
     * @var ChatMessage
     */
    public $message;


    /**
     * FriendChat constructor.
     * @param ChatMessage $message
     */
    public function __construct(ChatMessage $message)
    {
        $this->message = $message;
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
