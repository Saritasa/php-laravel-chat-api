<?php

namespace Saritasa\LaravelChatApi\Contracts;

use Saritasa\LaravelChatApi\Exceptions\ChatException;
use Saritasa\LaravelEntityServices\Exceptions\EntityServiceOperationException;

/**
 * Service to work with chat and chat participants.
 */
interface IChatService
{
    /**
     * Create new conversation.
     *
     * @param IChatUser $creator
     * @param array $chatData
     * @param array $userIds
     *
     * @return IChat
     *
     * @throws ChatException
     */
    public function createChat(IChatUser $creator, array $chatData, array $userIds): IChat;

    /**
     * Close conversation.
     *
     * @param IChatUser $sender
     * @param IChat $chat
     *
     * @throws ChatException
     * @throws EntityServiceOperationException
     */
    public function closeChat(IChatUser $sender, IChat $chat): void;

    /**
     * Send message in chat.
     *
     * @param IChatUser $sender
     * @param IChat $chat
     * @param string $message
     *
     * @return IChatMessage
     *
     * @throws ChatException
     */
    public function sendMessage(IChatUser $sender, IChat $chat, string $message): IChatMessage;

    /**
     * Leave chat.
     *
     * @param IChat $chat
     * @param IChatUser $chatUser
     *
     * @throws ChatException
     */
    public function leaveChat(IChat $chat, IChatUser $chatUser): void;

    /**
     * Mark chat as read for user.
     *
     * @param IChat $chat
     * @param IChatUser $chatUser
     *
     * @throws ChatException
     */
    public function markChatAsRead(IChat $chat, IChatUser $chatUser): void;
}
