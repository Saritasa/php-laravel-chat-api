<?php

namespace Saritasa\LaravelChatApi\Contracts;

use Illuminate\Support\Collection;

/**
 * Conversation between users.
 */
interface IChat
{
    /**
     * Get chat members.
     *
     * @return Collection|IChatUser[]
     */
    public function getUsers(): Collection;

    /**
     * Get identifier.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Get user which created this chat.
     *
     * @return IChatUser
     */
    public function getCreator(): IChatUser;

    /**
     * Check whether the user is a participant of the chat.
     *
     * @param IChatUser $chatUser User fot check is he participant of chat
     *
     * @return boolean
     */
    public function inChat(IChatUser $chatUser): bool;

    /**
     * Is chat already closed ?
     *
     * @return boolean
     */
    public function isClosed(): bool;

    /**
     * Get all chat messages.
     *
     * @return Collection|IChatMessage[]
     */
    public function getMessages(): Collection;
}
