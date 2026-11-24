<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Carbon;
use Laravel\Ai\Models\ConversationMessage as BaseMessage;

/**
 * @property string $id
 * @property string $conversation_id
 * @property string|null $participant_type
 * @property int|null $participant_id
 * @property string $agent
 * @property string $role
 * @property string $content
 * @property string|array<string, mixed> $attachments
 * @property string|array<string, mixed> $steps
 * @property string|array<string, mixed> $usage
 * @property string|array<string, mixed> $meta
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class AgentMessage extends BaseMessage
{
    protected $table = 'agent_conversation_messages';
}
