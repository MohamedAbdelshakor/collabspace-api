<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Carbon;
use Laravel\Ai\Models\Conversation as BaseConversation;

/**
 * @property string $id
 * @property string|null $participant_type
 * @property int|null $participant_id
 * @property string $title
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class AgentConversation extends BaseConversation
{
    protected $table = 'agent_conversations';
}
