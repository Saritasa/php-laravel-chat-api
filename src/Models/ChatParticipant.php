<?php

namespace Saritasa\Laravel\Chat\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Saritasa\Database\Eloquent\Entity;
use Saritasa\Database\Eloquent\Models\User;

/**
 * App\Model\Entities\ChatParticipant
 *
 * @property int $id
 * @property int $chat_id
 * @property int $user_id
 * @property bool $creator
 * @property bool $notification_off
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static Builder|ChatParticipant whereChatId($value)
 * @method static Builder|ChatParticipant whereCreatedAt($value)
 * @method static Builder|ChatParticipant whereCreator($value)
 * @method static Builder|ChatParticipant whereId($value)
 * @method static Builder|ChatParticipant whereNotificationOff($value)
 * @method static Builder|ChatParticipant whereUpdatedAt($value)
 * @method static Builder|ChatParticipant whereUserId($value)
 * @method static Builder|ChatParticipant whereIsRead($value)
 * @mixin \Eloquent
 * @property bool $is_read
 * @property-read User $user
 * @property Chat $chat
 */
class ChatParticipant extends Entity
{
    protected $fillable = [
        'chat_id',
        'user_id',
        'creator',
    ];

    protected $hidden = [
        'notification_off',
        'created_at',
        'updated_at',
        'creator',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }
}
