<?php

namespace Saritasa\LaravelChatApi\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Saritasa\LaravelChatApi\Contracts\IChatMessage;
use Saritasa\LaravelChatApi\Contracts\IChatUser;

/**
 * Message which user send in chat.
 *
 * @property int $id
 * @property int $chat_id
 * @property int $user_id
 * @property string $message
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property IChatUser $user User which one send this message in chat
 * @property Chat $chat Chat where this message is
 */
class ChatMessage extends Model implements IChatMessage
{
    public const CHAT_ID = 'chat_id';
    public const USER_ID = 'user_id';
    public const MESSAGE = 'message';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'update_at';

    protected $fillable = [
        self::CHAT_ID,
        self::USER_ID,
        self::MESSAGE,
    ];

    protected $dates = [
        self::CREATED_AT,
        self::UPDATED_AT,
    ];

    /**
     * User which one send this message in chat.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('laravelChatApi.userModelClass'));
    }

    /**
     * Get chat where this message is.
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
    public function getMessage(): string
    {
        return $this->message;
    }
}
