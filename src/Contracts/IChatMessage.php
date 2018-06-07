<?php

namespace Saritasa\LaravelChatApi\Contracts;

/**
 * Message in chat.
 */
interface IChatMessage
{
    /**
     * Get message text.
     *
     * @return string
     */
    public function getMessage(): string;

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
}
