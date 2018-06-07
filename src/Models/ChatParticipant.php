<?php

namespace Saritasa\LaravelChatApi\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Saritasa\LaravelChatApi\Contracts\IChat;
use Saritasa\LaravelChatApi\Contracts\IChatParticipant;
use Saritasa\LaravelChatApi\Contracts\IChatUser;

/**
 * Information about user participation in the chat.
 *
 * @property int $id Id
 * @property int $chat_id Chat identifier
 * @property int $user_id User identifier
 * @property bool $notification_off In user push off all notifications in chat
 * @property Carbon $created_at Date when user join in chat
 * @property Carbon $updated_at Update information date
 * @property bool $is_read All messages in chat are read ?
 *
 * @property-read IChatUser $user User who is participating in the chat
 * @property-read Chat $chat Chat between users
 */
class ChatParticipant extends Model implements IChatParticipant
{
    public const ID = 'id';
    public const IS_READ = 'is_read';
    public const USER_ID = 'user_id';
    public const CHAT_ID = 'chat_id';
    public const NOTIFICATION_OFF = 'notification_off';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const USER_RELATION = 'user';

    protected $with = [self::USER_RELATION];

    protected $fillable = [
        self::CHAT_ID,
        self::USER_ID,
        self::IS_READ,
    ];

    protected $hidden = [
        self::NOTIFICATION_OFF,
        self::CREATED_AT,
        self::UPDATED_AT,
    ];

    protected $dates = [
        self::CREATED_AT,
        self::UPDATED_AT,
    ];

    /**
     * User who is participating in the chat.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('laravel_chat_api.userModelClass'));
    }

    /**
     * Chat between users.
     *
     * @return BelongsTo
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getUser(): IChatUser
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function isNotificationOn(): bool
    {
        return !$this->notification_off;
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
    public function getChat(): IChat
    {
        return $this->chat;
    }

    /**
     * Return validation rules.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            static::IS_READ => 'bool',
            static::NOTIFICATION_OFF => 'bool',
            static::USER_ID => 'required|exists:' . config('laravel_chat_api.usersTable') . ',id',
            static::CHAT_ID => 'required|exists:chats,id',
        ];
    }
}
