<?php

namespace Saritasa\Laravel\Chat\Contracts;

/**
 * User who is a member of the chats.
 */
interface IChatUser
{
    /**
     * Get user identifier.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Get participant info in concrete conversation.
     *
     * @param IChat $chat Chat in which participant info need to get
     *
     * @return IChatParticipant
     */
    public function getChatParticipant(IChat $chat): IChatParticipant;
}
