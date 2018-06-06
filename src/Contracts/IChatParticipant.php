<?php

namespace Saritasa\Laravel\Chat\Contracts;

interface IChatParticipant
{
    public function getUser(): IChatUser;
    public function isNotificationOn(): bool;
    public function getId(): string;
}
