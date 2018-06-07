<?php

namespace Saritasa\Laravel\Chat\Contracts;

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
