<?php

namespace Saritasa\LaravelChatApi\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Saritasa\Laravel\Chat\Contracts\IChat;
use Saritasa\Laravel\Chat\Contracts\IChatParticipant;
use Saritasa\Laravel\Chat\Contracts\IChatUser;

/**
 * App\Model\Entities\ChatParticipant
 *
 * @property int $id
 * @property int $chat_id
 * @property int $user_id
 * @property bool $notification_off
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property bool $is_read
 *
 * @property-read IChatUser $user
 * @property-read IChat $chat
 */
class ChatParticipant extends Model implements IChatParticipant
{
    public const ID = 'id';
    public const IS_READ = 'is_read';
    public const USER_ID = 'user_id';
    public const CHAT_ID = 'chat_id';
    public const IS_CREATOR = 'is_creator';
    public const NOTIFICATION_OFF = 'notification_off';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    protected $with = [
        'user',
    ];

    protected $fillable = [
        self::CHAT_ID,
        self::USER_ID,
        self::IS_CREATOR,
        self::IS_READ,
    ];

    protected $hidden = [
        self::NOTIFICATION_OFF,
        self::IS_CREATOR,
        self::CREATED_AT,
        self::UPDATED_AT,
    ];

    protected $dates = [
        self::CREATED_AT,
        self::UPDATED_AT,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('laravelChatApi.userModelClass'));
    }

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
}
