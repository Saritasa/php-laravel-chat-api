<?php

namespace Saritasa\Laravel\Chat\Contracts;

interface IChatUser
{
    public function isFriendWith(IChatUser $possibleFriend): bool;
    public function getId(): string;
}
