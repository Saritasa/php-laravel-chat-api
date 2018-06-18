<?php

return [
    'userModelClass' => '',
    'usersTable' => 'users',
    'chatModelClass' => '',
    'chatsTable' => 'chats',
    'notifications' => [
        'chatClosed' => \Saritasa\LaravelChatApi\Notifications\ChatNotification::class,
        'newMessage' => \Saritasa\LaravelChatApi\Notifications\ChatNotification::class,
        'chatReopened' => \Saritasa\LaravelChatApi\Notifications\ChatNotification::class,
    ],
];
