<?php

namespace Saritasa\Laravel\Chat\Api;

use App\Extensions\CurrentApiUserTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Saritasa\Database\Eloquent\Models\User;
use Saritasa\DingoApi\BaseApiController;
use Saritasa\Laravel\Chat\Models\Chat;
use Saritasa\Laravel\Chat\Repositories\ChatRepository;

class ChatController extends BaseApiController
{
    use CurrentApiUserTrait;

    /**
     * @var ChatRepository
     */
    protected $chatRepo;

    /**
     * ChatController constructor.
     * @param $chatRepo
     */
    public function __construct(ChatRepository $chatRepo)
    {
        parent::__construct();
        $this->chatRepo = $chatRepo;
    }

    /**
     * Get conversation list
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList(Request $request)
    {
        list($limit, $page) = $this->getPagerInfo($request);
        $user = $this->user();
        $paginator = $this->chatRepo->getList($user, $limit, $page);
        return $this->pager($paginator);
    }

    /**
     * Create a chat conversation
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $sender = $this->user();
        $this->validate($request, [
            'user_id' => 'required|integer|exists:users,id|not_in:'.$sender->id,
        ]);
        $user = User::findOrFail($request->json('user_id'));
        $chat = $this->chatRepo->getOrCreate($sender, $user);
        return $this->json($chat);
    }

    /**
     * Get a conversation info
     *
     * @param Chat $chat
     * @return Chat
     */
    public function get(Chat $chat)
    {
        $user = $this->user();
        $this->chatRepo->validateChatMember($user, $chat);
        $chat->load('participants');
        return $chat;
    }

    /**
     * Send a message to a conversation
     *
     * @param Chat $chat
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage(Chat $chat, Request $request)
    {
        $sender = $this->user();
        $this->validate($request, [
            'message' => 'required|max:500',
        ]);
        $msg = $this->chatRepo->sendMessage($sender, $chat, $request->json('message'));
        return $this->json([
            'message' => trans('chats.sent'),
            'data' => $msg,
        ]);
    }

    /**
     * Get messages of a conversation
     *
     * @param Chat $chat
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMessages(Chat $chat, Request $request)
    {
        list($limit, $page) = $this->getPagerInfo($request);
        $last_time = $request->query('last_time', null);
        $last_time = $last_time ? Carbon::createFromFormat('Y-m-d H:i:s', $last_time) : null;

        $user = $this->user();

        $paginator = $this->chatRepo->getMessages($user, $chat, $last_time, $limit, $page);
        return $this->pager($paginator);
    }

    /**
     * Mark read status for a conversation
     *
     * @param Chat $chat
     * @return \Illuminate\Http\JsonResponse
     */
    public function readMessages(Chat $chat)
    {
        $this->chatRepo->changeReadStatus($this->user(), $chat, true);
        return $this->json(['message' => trans('chats.marked_read')]);
    }

    /**
     * Turn notification a conversation off
     *
     * @param Chat $chat
     * @return \Illuminate\Http\JsonResponse
     */
    public function leaveChat(Chat $chat)
    {
        $user = $this->user();
        $this->chatRepo->changeNotificationStatus($user, $chat);
        return $this->json(['message' => trans('chats.notification_off')]);

    }
}
