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
     * @param IChatUser $creator Creator of chat
     * @param array $chatData Chat information
     * @param array $userIds Chat participants ids
     *
     * @return IChat
     *
     * @throws ChatException
     */
    public function createChat(IChatUser $creator, array $chatData, array $userIds): IChat;

    /**
     * Close conversation.
     *
     * @param IChatUser $sender User which initiate closing
     * @param IChat $chat Chat which is closing
     *
     * @throws ChatException
     * @throws EntityServiceOperationException
     */
    public function closeChat(IChatUser $sender, IChat $chat): void;

    /**
     * Send message in chat.
     *
     * @param IChatUser $sender User which send new message in chat
     * @param IChat $chat Chat to which user user send message
     * @param string $message Message text
     *
     * @return IChatMessage
     *
     * @throws ChatException
     */
    public function sendMessage(IChatUser $sender, IChat $chat, string $message): IChatMessage;

    /**
     * Leave chat.
     *
     * @param IChat $chat Chat which leave by user
     * @param IChatUser $chatUser User User which leaves chat
     *
     * @throws ChatException
     */
    public function leaveChat(IChat $chat, IChatUser $chatUser): void;

    /**
     * Mark chat as read for user.
     *
     * @param IChat $chat Chat to mark as read for user
     * @param IChatUser $chatUser User which read chat messages
     *
     * @throws ChatException
     */
    public function markChatAsRead(IChat $chat, IChatUser $chatUser): void;
}
