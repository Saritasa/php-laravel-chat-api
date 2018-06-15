<?php

namespace Saritasa\LaravelChatApi\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Saritasa\LaravelChatApi\Contracts\IChat;
use Saritasa\LaravelChatApi\Contracts\IChatUser;

/**
 * App\Model\Entities\Chat
 *
 * @property int $id
 * @property string $name
 * @property bool $is_closed
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Collection|IChatUser[] $users
 * @property-read Collection|ChatMessage[] $messages
 * @property-read Collection|ChatParticipant[] $participants
 * @property-read IChatUser $createdBy
 */
class Chat extends Model implements IChat
{
    use SoftDeletes;

    public const NAME = 'name';
    public const IS_CLOSED = 'is_closed';
    public const CREATED_BY = 'created_by';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    protected $fillable = [
        self::NAME,
        self::IS_CLOSED,
        self::CREATED_BY,
        self::UPDATED_AT,
    ];

    public $dates = [
        self::CREATED_AT,
        self::UPDATED_AT,
    ];

    /**
     * Users which participant in chat.
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(config('laravel_chat_api.userModelClass'), 'chat_participants');
    }

    /**
     * Chat participants.
     *
     * @return HasMany
     */
    public function participants(): HasMany
    {
        return $this->hasMany(ChatParticipant::class);
    }

    /**
     * Chat creator.
     *
     * @return BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(config('laravel_chat_api.userModelClass'), static::CREATED_BY);
    }

    /**
     * Messages in this chat.
     *
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreator(): IChatUser
    {
        return $this->createdBy;
    }

    /**
     * {@inheritdoc}
     */
    public function inChat(IChatUser $chatUser): bool
    {
        return $this->users->contains($chatUser);
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): string
    {
        return $this->getKey();
    }

    /**
     * {@inheritdoc}
     */
    public function isClosed(): bool
    {
        return $this->is_closed;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    /**
     * Return validation rules.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            static::NAME => 'required|string',
            static::CREATED_BY => 'required|exists:' . config('laravel_chat_api.usersTable') . ',id',
            static::IS_CLOSED => 'bool',
        ];
    }
}
