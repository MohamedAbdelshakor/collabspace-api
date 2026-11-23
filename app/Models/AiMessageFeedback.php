<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMessageFeedback extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'ai_message_feedbacks';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ai_message_id',
        'user_id',
        'rating',
        'reason',
    ];

    /**
     * @return BelongsTo<AiMessage, $this>
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(AiMessage::class, 'ai_message_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
