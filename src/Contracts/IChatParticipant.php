<?php

namespace Saritasa\LaravelChatApi\Contracts;

/**
 * Information about user participation in the chat.
 */
interface IChatParticipant
{
    /**
     * Get user which participants in chat.
     *
     * @return IChatUser
     */
    public function getUser(): IChatUser;

    /**
     * Get chat.
     *
     * @return IChat
     */
    public function getChat(): IChat;

    /**
     * Is notification on in the chat ?
     *
     * @return boolean
     */
    public function isNotificationOn(): bool;

    /**
     * Get identifier.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Is chat read by user ?
     *
     * @return boolean
     */
    public function isRead(): bool;
}
