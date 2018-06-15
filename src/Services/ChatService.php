<?php

namespace Saritasa\LaravelChatApi\Services;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Saritasa\LaravelChatApi\Contracts\IChat;
use Saritasa\LaravelChatApi\Contracts\IChatMessage;
use Saritasa\LaravelChatApi\Contracts\IChatParticipant;
use Saritasa\LaravelChatApi\Contracts\IChatUser;
use Saritasa\LaravelChatApi\Contracts\INotificationsFactory;
use Saritasa\LaravelChatApi\Enums\NotificationsType;
use Saritasa\LaravelChatApi\Events\ChatClosedEvent;
use Saritasa\LaravelChatApi\Contracts\IChatService;
use Saritasa\LaravelChatApi\Events\ChatClosedUserEvent;
use Saritasa\LaravelChatApi\Events\ChatCreatedEvent;
use Saritasa\LaravelChatApi\Events\ChatLeavedEvent;
use Saritasa\LaravelChatApi\Events\MessageSentEvent;
use Saritasa\LaravelChatApi\Events\MessageSentUserEvent;
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
     * Notifications factory.
     *
     * @var INotificationsFactory
     */
    protected $notificationsFactory;

    /**
     * Service to work with chat and chat participants.
     *
     * @param IEntityServiceFactory $entityServiceFactory Entity services factory
     * @param Dispatcher $dispatcher Notifications dispatcher
     * @param ConnectionInterface $connection Connection interface realization
     * @param INotificationsFactory $notificationsFactory Notifications factory
     *
     * @throws EntityServiceException
     */
    public function __construct(
        IEntityServiceFactory $entityServiceFactory,
        Dispatcher $dispatcher,
        ConnectionInterface $connection,
        INotificationsFactory $notificationsFactory
    ) {
        $this->entityServiceFactory = $entityServiceFactory;
        $this->dispatcher = $dispatcher;
        $this->connection = $connection;
        $this->chatEntityService = $this->entityServiceFactory->build(config('laravel_chat_api.chatModelClass'));
        $this->participantEntityService = $this->entityServiceFactory->build(ChatParticipant::class);
        $this->notificationsFactory = $notificationsFactory;
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
            $chat = $this->chatEntityService->create(array_merge($chatData, [Chat::CREATED_BY => $creator->getId()]));

            $this->participantEntityService->create([
                ChatParticipant::USER_ID => $creator->getId(),
                ChatParticipant::NOTIFICATION_OFF => false,
                ChatParticipant::CHAT_ID => $chat->getId(),
                ChatParticipant::IS_READ => true,
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
                event(new ChatCreatedEvent($chat, $chatParticipant->getUser()));
            }

            return $chat;
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

        $this->handleTransaction(function () use ($chat, $sender) {
            $chatId = $chat->getId();
            $chatUsers = $chat->getUsers();
            /**
             * Chat to delete.
             *
             * @var Model $chat
             */
            $this->chatEntityService->update($chat, [
                Chat::IS_CLOSED => true,
            ]);
            event(new ChatClosedEvent($chatId));
            foreach ($chatUsers as $chatUser) {
                if ($chatUser->getId() === $sender->getId()) {
                    continue;
                }
                $this->dispatcher->sendNow(
                    [$chatUser],
                    $this->notificationsFactory->build(NotificationsType::CHAT_CLOSED)
                );
                event(new ChatClosedUserEvent($chatUser->getId(), $chatId));
            }
        });
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
            event(new MessageSentEvent($chat, $sender, $chatMessage));
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
                    $this->dispatcher->sendNow(
                        [$chatUser],
                        $this->notificationsFactory->build(NotificationsType::NEW_MESSAGE)
                    );
                }
                $this->participantEntityService->update($chatParticipant, [ChatParticipant::IS_READ => false]);
                event(new MessageSentUserEvent($chatUser, $chat, $sender, $chatMessage));
            }

            /**
             * Chat to update.
             *
             * @var Model $chat
             */
            $this->chatEntityService->update($chat, [Chat::UPDATED_AT => Carbon::now()]);


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

            if ($chat->getCreator() === $chatUser) {
                $newCreator = null;
                foreach ($chat->getUsers() as $user) {
                    if ($user !== $chatUser) {
                        $newCreator = $user;
                        break;
                    }
                }
                /**
                 * Chat participant.
                 *
                 * @var Chat $chat
                 */
                $this->chatEntityService->update($chat, [Chat::CREATED_BY => $newCreator->getId()]);
            }

            event(new ChatLeavedEvent($chat, $chatUser));
        });
    }

    /**
     * {@inheritdoc}
     */
    public function markChatAsRead(IChat $chat, IChatUser $chatUser): void
    {
        if (!$chat->inChat($chatUser) || $chat->isClosed()) {
            throw new ChatException('chats.leave_error');
        }

        $this->handleTransaction(function () use ($chat, $chatUser) {
            /**
             * Chat participant.
             *
             * @var ChatParticipant $chatParticipant
             */
            $chatParticipant = $chatUser->getChatParticipant($chat);
            $this->participantEntityService->update($chatParticipant, [
                ChatParticipant::IS_READ => true,
            ]);
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
