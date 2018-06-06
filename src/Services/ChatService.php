<?php

namespace Saritasa\Laravel\Chat\Services;

use Closure;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;
use Saritasa\Laravel\Chat\Contracts\IChat;
use Saritasa\Laravel\Chat\Contracts\IChatMessage;
use Saritasa\Laravel\Chat\Contracts\IChatParticipant;
use Saritasa\Laravel\Chat\Contracts\IChatUser;
use Saritasa\Laravel\Chat\Events\ChatClosed;
use Saritasa\Laravel\Chat\Notifications\NewMessageNotification;
use Saritasa\LaravelChatApi\Events\ChatCreated;
use Saritasa\LaravelChatApi\Events\MessageSent;
use Saritasa\LaravelChatApi\Exceptions\ChatException;
use Saritasa\LaravelChatApi\Models\ChatMessage;
use Saritasa\LaravelChatApi\Models\ChatParticipant;
use Saritasa\LaravelEntityServices\Contracts\IEntityService;
use Saritasa\LaravelEntityServices\Contracts\IEntityServiceFactory;
use Throwable;

class ChatService
{
    protected $appChatModelClass = null;

    /**
     * @var IEntityServiceFactory
     */
    protected $entityServiceFactory;

    /**
     * @var IEntityService
     */
    protected $chatEntityService;

    /**
     * @var IEntityService
     */
    protected $participantEntityService;

    protected $dispatcher;
    /**
     * @var ConnectionInterface
     */
    private $connection;

    public function __construct(IEntityServiceFactory $entityServiceFactory, Dispatcher $dispatcher, ConnectionInterface $connection)
    {
        $this->entityServiceFactory = $entityServiceFactory;
        $this->dispatcher = $dispatcher;
        $this->connection = $connection;
        $this->chatEntityService = $this->entityServiceFactory->build(config('paymentSystem.userModelClass'));
        $this->participantEntityService = $this->entityServiceFactory->build(ChatParticipant::class);
    }

    /**
     * @param IChatUser $creator
     * @param array $chatData
     * @param Collection $userIds
     *
     * @return IChat
     *
     * @throws ChatException
     */
    public function createChat(IChatUser $creator, array $chatData, Collection $userIds): IChat
    {
        return $this->handleTransaction(function () use ($creator, $chatData, $userIds) {
            /**
             * Created chat.
             *
             * @var IChat $chat
             */
            $chat = $this->chatEntityService->create($chatData);

            $this->participantEntityService->create([
                ChatParticipant::USER_ID => $creator->getId(),
                ChatParticipant::NOTIFICATION_OFF => false,
                ChatParticipant::CHAT_ID => $chat->getId(),
                ChatParticipant::IS_READ => false,
                ChatParticipant::IS_CREATOR => true,
            ]);

            foreach ($userIds as $id) {
                $chatParticipant = $this->participantEntityService->create([
                    ChatParticipant::USER_ID => $id,
                    ChatParticipant::NOTIFICATION_OFF => false,
                    ChatParticipant::CHAT_ID => $chat->getId(),
                    ChatParticipant::IS_READ => false,
                ]);
                event(new ChatCreated($chat, $chatParticipant));
            }
        });
    }

    public function closeChat(IChatUser $sender, IChat $chat): void
    {
        if ($chat->getCreator()->getId() !== $sender->getId()) {
            throw new ChatException();
        }
        $chatId = $chat->getId();

        $participants = $chat->getParticipants();

        $this->chatEntityService->delete($chat);
        event(new ChatClosed($chatId));
        foreach ($participants as $chatParticipant) {
            if ($chatParticipant->getUser()->getId() === $sender->getId()) {
                continue;
            }
            $this->dispatcher->sendNow([$chatParticipant], new NewMessageNotification());

        }
    }

    /**
     * @param IChatUser $sender
     * @param IChat $chat
     * @param string $message
     * @return IChatMessage
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Saritasa\LaravelEntityServices\Exceptions\EntityServiceException
     * @throws \Saritasa\LaravelEntityServices\Exceptions\EntityServiceOperationException
     */
    public function sendMessage(IChatUser $sender, IChat $chat, string $message): IChatMessage
    {
        if (!$chat->inChat($sender)) {
            throw new ChatException();
        }

        /**
         * Chat message.
         *
         * @var ChatMessage $chatMessage
         */
        $chatMessage = $this->entityServiceFactory->build(ChatMessage::class)->create([
            'user_id' => $sender->getId(),
            'chat_id' => $chat->getId(),
            'message' => $message
        ]);

        event(new MessageSent($chat, $sender, $chatMessage));

        foreach ($chat->getParticipants() as $chatParticipant) {
            if ($chatParticipant->getUser()->getId() === $sender->getId()) {
                continue;
            }
            if ($chatParticipant->isNotificationOn()) {
                $this->dispatcher->sendNow([$chatParticipant], new NewMessageNotification());
            }
            $this->changeReadStatus($chatParticipant);
        }

        return $chatMessage;
    }

    public function changeReadStatus(IChatParticipant $chatParticipant, bool $isRead = false): void
    {
        $chatParticipantsEntityService = $this->entityServiceFactory->build(ChatParticipant::class);
        $chatParticipantsEntityService->update($chatParticipant, [ChatParticipant::IS_READ => $isRead]);
    }

    public function leaveChat(IChat $chat, IChatUser $chatUser): void
    {

    }

    protected function handleTransaction(Closure $callback)
    {
        try {
            $this->connection->beginTransaction();
            return tap($callback(), function () {
                $this->connection->commit();
            });
        } catch (Throwable $exception) {
            $this->connection->rollBack();
            throw new ChatException($exception->getMessage(), $exception);
        }
    }
}
