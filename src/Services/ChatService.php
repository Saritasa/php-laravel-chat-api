<?php

namespace Saritasa\LaravelChatApi\Services;

use Closure;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Database\ConnectionInterface;
use Saritasa\LaravelChatApi\Contracts\IChat;
use Saritasa\LaravelChatApi\Contracts\IChatMessage;
use Saritasa\LaravelChatApi\Contracts\IChatParticipant;
use Saritasa\LaravelChatApi\Contracts\IChatUser;
use Saritasa\LaravelChatApi\Events\ChatClosed;
use Saritasa\LaravelChatApi\Notifications\ChatClosedNotification;
use Saritasa\LaravelChatApi\Notifications\NewMessageNotification;
use Saritasa\LaravelChatApi\Contracts\IChatService;
use Saritasa\LaravelChatApi\Events\ChatCreated;
use Saritasa\LaravelChatApi\Events\LeaveChat;
use Saritasa\LaravelChatApi\Events\MessageSent;
use Saritasa\LaravelChatApi\Exceptions\ChatException;
use Saritasa\LaravelChatApi\Models\Chat;
use Saritasa\LaravelChatApi\Models\ChatMessage;
use Saritasa\LaravelChatApi\Models\ChatParticipant;
use Saritasa\LaravelEntityServices\Contracts\IEntityService;
use Saritasa\LaravelEntityServices\Contracts\IEntityServiceFactory;
use Saritasa\LaravelEntityServices\Exceptions\EntityServiceException;
use Throwable;

/**
 * Service to work with chat and chat participants.
 */
class ChatService implements IChatService
{
    /**
     * Factory of entities services.
     *
     * @var IEntityServiceFactory
     */
    protected $entityServiceFactory;

    /**
     * Chat entity service.
     *
     * @var IEntityService
     */
    protected $chatEntityService;

    /**
     * Participant entity service.
     *
     * @var IEntityService
     */
    protected $participantEntityService;

    /**
     * Notifications dispatcher.
     *
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * Connection interface realization.
     *
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * Service to work with chat and chat participants.
     *
     * @param IEntityServiceFactory $entityServiceFactory
     * @param Dispatcher $dispatcher
     * @param ConnectionInterface $connection
     *
     * @throws EntityServiceException
     */
    public function __construct(
        IEntityServiceFactory $entityServiceFactory,
        Dispatcher $dispatcher,
        ConnectionInterface $connection
    ) {
        $this->entityServiceFactory = $entityServiceFactory;
        $this->dispatcher = $dispatcher;
        $this->connection = $connection;
        $this->chatEntityService = $this->entityServiceFactory->build(config('paymentSystem.userModelClass'));
        $this->participantEntityService = $this->entityServiceFactory->build(ChatParticipant::class);
    }

    /**
     * {@inheritdoc}
     */
    public function createChat(IChatUser $creator, array $chatData, array $userIds): IChat
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
            ]);

            foreach ($userIds as $id) {
                /**
                 * Created chat participant.
                 *
                 * @var IChatParticipant $chatParticipant
                 */
                $chatParticipant = $this->participantEntityService->create([
                    ChatParticipant::USER_ID => $id,
                    ChatParticipant::NOTIFICATION_OFF => false,
                    ChatParticipant::CHAT_ID => $chat->getId(),
                    ChatParticipant::IS_READ => false,
                ]);
                event(new ChatCreated($chat, $chatParticipant->getUser()));
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function closeChat(IChatUser $sender, IChat $chat): void
    {
        if ($chat->getCreator()->getId() !== $sender->getId() || $chat->isClosed()) {
            throw new ChatException(trans('chats.close_error'));
        }
        $chatId = $chat->getId();

        $chatUsers = $chat->getUsers();
        /**
         * Chat to delete.
         *
         * @var Chat $chat
         */
        $this->chatEntityService->delete($chat);
        event(new ChatClosed($chatId));
        foreach ($chatUsers as $chatUser) {
            if ($chatUser->getId() === $sender->getId()) {
                continue;
            }
            $this->dispatcher->sendNow([$chatUser], new ChatClosedNotification());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sendMessage(IChatUser $sender, IChat $chat, string $message): IChatMessage
    {
        if (!$chat->inChat($sender) || $chat->isClosed()) {
            throw new ChatException(trans('chats.send_error'));
        }

        return $this->handleTransaction(function () use ($sender, $chat, $message) {
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

            foreach ($chat->getUsers() as $chatUser) {
                if ($chatUser->getId() === $sender->getId()) {
                    continue;
                }

                /**
                 * Chat participant.
                 *
                 * @var ChatParticipant $chatParticipant
                 */
                $chatParticipant = $chatUser->getChatParticipant($chat);

                if ($chatParticipant->isNotificationOn()) {
                    $this->dispatcher->sendNow([$chatUser], new NewMessageNotification());
                }
                $this->participantEntityService->update($chatParticipant, [ChatParticipant::IS_READ => false]);
            }

            return $chatMessage;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function leaveChat(IChat $chat, IChatUser $chatUser): void
    {
        if (!$chat->inChat($chatUser) || $chat->isClosed()) {
            throw new ChatException('chats.leave_error');
        }

        $this->handleTransaction(function () use ($chat, $chatUser) {
            $chatParticipant = $this->participantEntityService->getRepository()->findWhere([
                ChatParticipant::USER_ID => $chatUser->getId(),
                ChatParticipant::CHAT_ID => $chat->getId(),
            ]);

            $this->participantEntityService->delete($chatParticipant);

            event(new LeaveChat($chat, $chatUser));
        });
    }

    /**
     * Wrap closure in db transaction.
     *
     * @param Closure $callback Callback which will be wrapped into transaction
     *
     * @return mixed
     *
     * @throws ChatException
     */
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