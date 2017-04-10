<?php

namespace Saritasa\Laravel\Chat\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Saritasa\Database\Eloquent\Entity;
use Saritasa\Database\Eloquent\Models\User;

/**
 * App\Model\Entities\Chat
 *
 * @property int $id
 * @property string $name
 * @property Collection $notification_off
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static Builder|Chat whereCreatedAt($value)
 * @method static Builder|Chat whereId($value)
 * @method static Builder|Chat whereName($value)
 * @method static Builder|Chat whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read null $is_read
 * @property-read Collection|User[] $participants
 * @property-read Collection|ChatMessage[] $messages
 */
class Chat extends Entity
{
    protected $fillable = [
        'name'
    ];

    protected $hidden = [];

    protected $appends = [
        'is_read',
        'notification_off',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function participants()
    {
        return $this->belongsToMany(User::class, 'chat_participants');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * @return null
     */
    public function getIsReadAttribute()
    {
        $currentUser = \Auth::user();
        if ($currentUser) {
            $chatParticipant = $this->hasMany(ChatParticipant::class)->where('user_id', $currentUser->id)->first();
            return $chatParticipant ? $chatParticipant->is_read : null;
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getNotificationOffAttribute()
    {
        return $this->hasMany(ChatParticipant::class)
            ->where('notification_off', 1)
            ->select('user_id')
            ->get();
    }

    /**
     * @param User $sender
     * @return User|null
     */
    public function getReceiver(User $sender)
    {
        return $this->participants()
            ->wherePivot('user_id', '<>', $sender->id)
            ->first();
    }

    
}
