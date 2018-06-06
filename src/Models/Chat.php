<?php

namespace Saritasa\LaravelChatApi\Models;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Saritasa\Laravel\Chat\Contracts\IChat;
use Saritasa\Laravel\Chat\Contracts\IChatParticipant;
use Saritasa\Laravel\Chat\Contracts\IChatUser;

/**
 * App\Model\Entities\Chat
 *
 * @property int $id
 * @property string $name
 * @property bool $is_closed
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection|User[] $participants
 * @property-read Collection|ChatMessage[] $messages
 */
class Chat extends Model implements IChat
{
    use SoftDeletes;

    protected $fillable = [
        'name'
    ];

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
     * @return \Illuminate\Support\Collection|IChatParticipant[]
     */
    public function getParticipants(): Collection
    {
        // TODO: Implement getParticipants() method.
    }

    public function getCreator(): IChatUser
    {
        // TODO: Implement getCreator() method.
    }

    public function inChat(IChatUser $chatUser): bool
    {
        // TODO: Implement inChat() method.
    }
}
