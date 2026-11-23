<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MeetingAttendeeResponse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MeetingAttendee extends Pivot
{
    /**
     * @var string
     */
    protected $table = 'meeting_attendees';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'meeting_id',
        'user_id',
        'response',
        'joined_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'response' => MeetingAttendeeResponse::class,
            'joined_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Meeting, $this>
     */
    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class, 'meeting_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
