<?php


namespace Saritasa\Laravel\Chat\Events;


use App\Model\Entities\Chat;
use App\Model\Entities\ChatMessage;
use App\Model\Entities\User;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * Event fires, when new message is posted to pool chat
 *
 * @package App\Events
 */
class MessageCreated extends ChatEvent implements ShouldBroadcast
{
    protected $channel;

    public $message;

    public function __construct(User $sender, ChatMessage $chatMessage, Chat $chat)
    {
        $receiver = $chat->getReceiver($sender);
        $this->channel = $receiver->pusher_channel;
        $this->message = $chatMessage->load('user')->toArray();
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
