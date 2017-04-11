<?php

namespace Saritasa\Laravel\Chat\Repositories;

use Saritasa\Laravel\Chat\Models\Chat;
use Saritasa\Laravel\Chat\Models\ChatMessage;
use Saritasa\Laravel\Chat\Models\ChatParticipant;
use Carbon\Carbon;
use Saritasa\Database\Eloquent\Models\User;
use Saritasa\Exceptions\NotFoundException;

class ChatRepository
{
    /**
     * @param $sender
     * @param $user
     *
     * @return Chat
     */
    public function getOrCreate(User $sender, User $user)
    {
        $chat = $this->get($sender, $user);
        if (!$chat) {
            $chat= $this->create($sender, $user);
        } elseif ($chat->notification_off->contains('user_id', $sender->id)) {
            $this->changeNotificationStatus($sender, $chat, false);
        }
        return $chat;
    }

    /**
     * Validate sender send message to block user or not
     *
     * @param User $sender
     * @param User $receiver
     * @throws NotFoundException
     */
    public function validateBlockedUser(User $sender, $receiver)
    {
        if (!$receiver || $sender->blocked($receiver)) {
            throw new NotFoundException(trans('chats.receiver_not_found'));
        }
    }

    /**
     * Validate user was deleted or not
     *
     * @param User $user
     * @throws NotFoundException
     */
    public function validateDeletedUser(User $user)
    {
        if ($user->deleted_at != null) {
            throw new NotFoundException(trans('users.not_found'));
        }
    }

    /**
     * Validate sender is a chat member or not
     *
     * @param User $sender
     * @param Chat $chat
     * @throws NotFoundException
     */
    public function validateChatMember(User $sender, Chat $chat)
    {
        if (!$sender->inChat($chat)) {
            throw new NotFoundException(trans('chats.not_found'));
        }
    }

    /**
     * @param User $sender
     * @param User $user
     * @throws NotFoundException
     * @return Chat
     */
    public function create(User $sender, User $user)
    {
        $this->validateBlockedUser($sender, $user);
        $chat = null;
        DB::beginTransaction();
        try {
            $chat = Chat::create([
                'name' => $sender->id . ' - ' . $user->id
            ]);
            ChatParticipant::create([
                'chat_id' => $chat->id,
                'user_id' => $sender->id,
                'creator' => true,
                'is_read' => true,
            ]);
            ChatParticipant::create([
                'chat_id' => $chat->id,
                'user_id' => $user->id,
            ]);
            DB::commit();
            event(new ChatCreated($chat, $sender));
        }
        catch(\Exception $ex) {
            DB::rollBack();
            throw new Exception(trans('chats.create_fail'), null, 0, $ex);
        }
        return $chat ? $chat->with('participants')->find($chat->id) : null;
    }

    /**
     * Get a conversion between 2 users
     *
     * @param User $sender
     * @param User $user
     * @return Chat
     */
    public function get(User $sender, User $user)
    {
        $this->validateDeletedUser($user);
        $this->validateBlockedUser($sender, $user);
        $chat = ChatParticipant::whereIn('user_id', [$sender->id, $user->id])
            ->select('chat_id')
            ->addSelect(\DB::raw('count(user_id) as count'))
            ->groupBy('chat_id')
            ->havingRaw('count(user_id) > 1')
            ->first();
        $chat = $chat ? Chat::with('participants')->find($chat->chat_id) : null;
        return $chat;
    }

    /**
     * Get conversions of a user
     *
     * @param User $user
     * @param int $limit
     * @param int $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getList(User $user, int $limit = 100, int $page = 0)
    {
        $chatIds = ChatParticipant::where('chat_participants.user_id', $user->id)
            ->join('chat_participants as p', function($join) use ($user) {
                $join->on('p.chat_id', '=', 'chat_participants.chat_id')
                    ->where('p.user_id', '<>', $user->id);
            })
            ->whereNotIn('p.user_id', function($query) use ($user) {
                $query->select('blocked_user_id')
                    ->from('black_lists')
                    ->where('owner_id', $user->id);
            })
            ->pluck('p.chat_id');
        return Chat::with(['participants'])
            ->whereIn('chats.id', $chatIds)
            ->paginate($limit, ['*'], null, $page);
    }

    /**
     * Send message to a conversation
     *
     * @param User $sender
     * @param Chat $chat
     * @param string $message
     *
     * @return ChatMessage
     */
    public function sendMessage(User $sender, Chat $chat, string $message)
    {
        $receiver = $chat->getReceiver($sender);
        $this->validateDeletedUser($receiver);
        $this->validateBlockedUser($sender, $receiver);
        $this->validateChatMember($sender, $chat);
        $chatMessage = ChatMessage::create([
            'user_id' => $sender->id,
            'chat_id' => $chat->id,
            'message' => $message
        ]);
        event(new MessageCreated($sender, $chatMessage, $chat));
        if (!$this->isNotificationOff($receiver, $chat)) {
            if ($sender->isFriendWith($receiver)) {
                event(new FriendChat($chatMessage));
            }
        }
        $this->changeReadStatus($receiver, $chat, false);
        return $chatMessage;
    }

    /**
     * Get messages of a conversation
     *
     * @param User $user
     * @param Chat $chat
     * @param null $last_time
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function getMessages(User $user, Chat $chat, $last_time = null, $limit = 100, $page = 0)
    {
        $this->validateChatMember($user, $chat);
        $query = $chat->messages();
        if ($last_time instanceof Carbon) {
            $query->where('created_at', '>', $last_time);
        }
        return $query->paginate($limit, ['*'], null, $page);
    }

    /**
     * Mark read status for a chat conversation
     * 
     * @param User $user
     * @param Chat $chat
     * @param bool $isRead
     *
     * @return bool|int
     */
    public function changeReadStatus(User $user, Chat $chat, bool $isRead)
    {
        $this->validateChatMember($user, $chat);
        return ChatParticipant::whereChatId($chat->id)
            ->whereUserId($user->id)
            ->update(['is_read' => $isRead]);
    }

    /**
     * Turn notification a chat conversation off
     *
     * @param User $user
     * @param Chat $chat
     * @param bool $off
     * @return bool
     * @throws NotFoundException
     */
    public function changeNotificationStatus(User $user, Chat $chat, bool $off = true)
    {
        $this->validateChatMember($user, $chat);
        /** @var ChatParticipant $chatParticipant */
        $chatParticipant = $chat->hasMany(ChatParticipant::class)
            ->where('user_id', $user->id)->first();
        if (!$chatParticipant) {
            throw new NotFoundException(trans('chats.not_found'));
        }
        $chatParticipant->notification_off = $off;
        return $chatParticipant->save();
    }

    /**
     * @param User $user
     * @param Chat $chat
     * @return bool
     */
    public function isNotificationOff(User $user, Chat $chat)
    {
        $chatParticipant = $chat->hasMany(ChatParticipant::class)
            ->where('user_id', $user->id)->first();
        return $chatParticipant ? $chatParticipant->notification_off : false;
    }
}
