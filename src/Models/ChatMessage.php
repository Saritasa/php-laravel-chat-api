<?php

namespace Saritasa\LaravelChatApi\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Saritasa\LaravelChatApi\Contracts\IChat;
use Saritasa\LaravelChatApi\Contracts\IChatMessage;
use Saritasa\LaravelChatApi\Contracts\IChatUser;

/**
 * Message which user send in chat.
 *
 * @property int $id Id
 * @property int $chat_id Chat identifier
 * @property int $user_id User identifier
 * @property string $message Message text
 * @property Carbon $created_at Date when user join in chat
 * @property Carbon $updated_at Update information date
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
        return $this->belongsTo(config('laravel_chat_api.userModelClass'));
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
            static::MESSAGE => 'required|string',
            static::USER_ID => 'required|exists:' . config('laravel_chat_api.usersTable') . ',id',
            static::CHAT_ID => 'required|exists:chats,id',
        ];
    }
}
