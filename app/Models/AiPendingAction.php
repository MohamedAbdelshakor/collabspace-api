<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AiPendingActionStatus;
use App\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiPendingAction extends Model
{
    use HasFactory, HasUlid;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'tool',
        'payload',
        'status',
        'expires_at',
        'confirmed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'status' => AiPendingActionStatus::class,
            'expires_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
