<?php

namespace Saritasa\Laravel\Chat\Contracts;

use Illuminate\Support\Collection;

interface IChat
{
    /**
     * @return Collection|IChatParticipant[]
     */
    public function getParticipants(): Collection;
    public function getId(): string;
    public function getCreator(): IChatUser;
    public function inChat(IChatUser $chatUser): bool;
}
