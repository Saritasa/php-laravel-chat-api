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
}
