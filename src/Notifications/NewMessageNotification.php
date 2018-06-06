<?php

namespace Saritasa\Laravel\Chat\Notifications;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification implements ShouldBroadcastNow
{

}
