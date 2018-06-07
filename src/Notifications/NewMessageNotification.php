<?php

namespace Saritasa\LaravelChatApi\Notifications;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification implements ShouldBroadcastNow
{

}
