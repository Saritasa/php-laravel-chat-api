<?php

namespace Saritasa\LaravelChatApi\Notifications;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Notification;

class ChatClosedNotification extends Notification implements ShouldBroadcastNow
{

}
