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
     * @return bool
     */
    public function isNotificationOn(): bool;

    /**
     * Get identifier.
     *
     * @return string
     */
    public function getId(): string;
}
