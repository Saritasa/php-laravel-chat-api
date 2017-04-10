<?php

namespace Saritasa\Laravel\Chat\Events;

use App\Model\Entities\Chat;
use App\Model\Entities\User;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class LeaveChat extends ChatEvent implements ShouldBroadcast
{
    use SerializesModels;

    protected $channel;

    public $action;

    public function __construct(User $sender, Chat $chat)
    {
        $receiver = $chat->getReceiver($sender);
        $this->channel = $receiver->pusher_channel;
        $this->action = [
            'type' => 'leave',
            'user_id' => $sender->id,
            'chat_id' => $chat->id,
        ];
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [static::EVENT_MESSAGE_CREATED . $this->channel];
    }
}
