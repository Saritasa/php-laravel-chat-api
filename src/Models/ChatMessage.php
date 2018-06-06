<?php

namespace Saritasa\LaravelChatApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Saritasa\Laravel\Chat\Contracts\IChatMessage;

/**
 * App\Model\Entities\ChatMessage
 *
 * @property integer $id
 * @property integer $chat_id
 * @property integer $user_id
 * @property string $message
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property User $user
 * @property Chat $chat
 * @method static Builder|ChatMessage whereId($value)
 * @method static Builder|ChatMessage whereUserId($value)
 * @method static Builder|ChatMessage whereMessage($value)
 * @method static Builder|ChatMessage whereChatId($value)
 * @method static Builder|ChatMessage whereCreatedAt($value)
 * @method static Builder|ChatMessage whereUpdatedAt($value)
 */
class ChatMessage extends Model implements IChatMessage
{
    public $timestamps = true;

    protected $fillable = [
        'chat_id',
        'user_id',
        'message',
    ];

    protected $guarded = [];

    /**
     * Return common validation rules of all user fields
     *
     * @return array
     */
    public static function authRules()
    {
        return [
            'message' => 'required|max:500',
        ];
    }

    /**
     * Get user object
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get chat object
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function getMessage(): string
    {
        return $this->getMessage();
    }
}
