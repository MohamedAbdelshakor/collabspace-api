<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AvailabilityStatus;
use App\Models\Concerns\HasUlid;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, HasUlid, InteractsWithMedia, Notifiable, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar_path',
        'bio',
        'phone_country',
        'phone_number',
        'job_title',
        'years_of_experience',
        'availability_status',
        'primary_project_id',
        'timezone',
        'locale',
        'pending_email',
        'last_active_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'availability_status' => AvailabilityStatus::class,
            'years_of_experience' => 'integer',
            'last_active_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function primaryProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'primary_project_id');
    }

    /**
     * @return BelongsToMany<Project, $this>
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_members')
            ->using(ProjectMember::class)
            ->withPivot(['id', 'department_id', 'role_title', 'is_leader', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<Project, $this>
     */
    public function ownedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    /**
     * @return BelongsToMany<Task, $this>
     */
    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_assignees')
            ->withPivot('assigned_at');
    }

    /**
     * @return HasMany<Task, $this>
     */
    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    /**
     * @return HasMany<Task, $this>
     */
    public function reviewedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'reviewer_id');
    }

    /**
     * @return HasMany<TimeLog, $this>
     */
    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class, 'user_id');
    }

    /**
     * @return BelongsToMany<Conversation, $this>
     */
    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->using(ConversationParticipant::class)
            ->withPivot(['last_read_message_id', 'muted_at', 'joined_at']);
    }

    /**
     * @return HasMany<Message, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'user_id');
    }

    /**
     * @return HasMany<Meeting, $this>
     */
    public function organizedMeetings(): HasMany
    {
        return $this->hasMany(Meeting::class, 'organizer_id');
    }

    /**
     * @return BelongsToMany<Meeting, $this>
     */
    public function attendingMeetings(): BelongsToMany
    {
        return $this->belongsToMany(Meeting::class, 'meeting_attendees')
            ->using(MeetingAttendee::class)
            ->withPivot(['response', 'joined_at']);
    }

    /**
     * @return HasMany<Report, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'user_id');
    }

    /**
     * Scope to find user by ULID.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeWhereUlid(Builder $query, string $ulid): void
    {
        $query->where('ulid', $ulid);
    }
}
