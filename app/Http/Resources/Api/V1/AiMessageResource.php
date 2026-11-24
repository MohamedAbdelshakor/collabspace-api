<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\AgentMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AgentMessage
 */
final class AiMessageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $meta = is_array($this->meta) ? $this->meta : (json_decode((string) $this->meta, true) ?: []);
        $linkPreview = $meta['link_preview'] ?? null;
        $feedback = $meta['feedback'] ?? null;

        $isAssistant = $this->role === 'assistant';
        $user = $request->user();

        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'role' => $this->role,
            'sender' => $isAssistant ? 'CB' : ($user instanceof User ? $user->name : 'User'),
            'content' => $this->content,
            'link_preview' => $linkPreview,
            'feedback' => $feedback,
            'actions' => $isAssistant ? ['speak', 'copy', 'regenerate', 'thumbs_down'] : [],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
