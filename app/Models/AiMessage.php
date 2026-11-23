<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AiMessageRole;
use App\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiMessage extends Model
{
    use HasFactory, HasUlid;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ai_conversation_id',
        'role',
        'content',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => AiMessageRole::class,
            'meta' => 'array',
        ];
    }

    /**
     * @return BelongsTo<AiConversation, $this>
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'ai_conversation_id');
    }

    /**
     * @return HasMany<AiMessageFeedback, $this>
     */
    public function feedbacks(): HasMany
    {
        return $this->hasMany(AiMessageFeedback::class, 'ai_message_id');
    }
}
