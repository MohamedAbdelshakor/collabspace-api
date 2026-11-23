<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MeetingRecordingStatus;
use App\Enums\MeetingStatus;
use App\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Meeting extends Model implements HasMedia
{
    use HasFactory, HasUlid, InteractsWithMedia, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'project_id',
        'organizer_id',
        'conversation_id',
        'title',
        'description',
        'starts_at',
        'ends_at',
        'meeting_url',
        'status',
        'recording_status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => MeetingStatus::class,
            'recording_status' => MeetingRecordingStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    /**
     * @return BelongsTo<Conversation, $this>
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'meeting_attendees')
            ->using(MeetingAttendee::class)
            ->withPivot(['response', 'joined_at']);
    }

    /**
     * @return HasMany<TranscriptSegment, $this>
     */
    public function transcriptSegments(): HasMany
    {
        return $this->hasMany(TranscriptSegment::class, 'meeting_id');
    }

    /**
     * @return HasMany<MeetingSummary, $this>
     */
    public function summaries(): HasMany
    {
        return $this->hasMany(MeetingSummary::class, 'meeting_id');
    }

    /**
     * @return HasMany<MeetingNote, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(MeetingNote::class, 'meeting_id');
    }

    /**
     * @return MorphMany<Comment, $this>
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
