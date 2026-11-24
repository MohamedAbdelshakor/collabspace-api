<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Enums\AvailabilityStatus;
use App\Enums\ConversationType;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Conversation
 */
final class ConversationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User|null $currentUser */
        $currentUser = $request->user();

        $displayTitle = $this->name;
        $displayAvatar = null;
        $partnerStatus = null;

        if ($this->type === ConversationType::Direct && $currentUser) {
            /** @var User|null $otherParticipant */
            $otherParticipant = $this->participants->firstWhere('id', '!==', $currentUser->id);
            if ($otherParticipant) {
                $displayTitle = $otherParticipant->name;
                $displayAvatar = $otherParticipant->avatar_path;
                $status = $otherParticipant->availability_status;
                $partnerStatus = $status instanceof AvailabilityStatus ? $status->value : (string) $status;
            }
        }

        $unreadCount = 0;
        if ($currentUser && $this->relationLoaded('participants')) {
            /** @var User|null $myParticipantRecord */
            $myParticipantRecord = $this->participants->firstWhere('id', '===', $currentUser->id);
            /** @var Pivot|null $pivot */
            $pivot = $myParticipantRecord?->getAttribute('pivot');
            $lastReadMessageId = (int) ($pivot?->getAttribute('last_read_message_id') ?? 0);

            if ($this->last_message_id && $this->last_message_id > $lastReadMessageId) {
                $unreadCount = Message::where('conversation_id', $this->id)
                    ->where('id', '>', $lastReadMessageId)
                    ->where('user_id', '!=', $currentUser->id)
                    ->count();
            }
        }

        $lastMessageAt = $this->last_message_at;

        return [
            'id' => $this->ulid,
            'type' => $this->type instanceof \BackedEnum ? $this->type->value : (string) $this->type,
            'name' => $this->name,
            'display_title' => $displayTitle,
            'display_avatar' => $displayAvatar,
            'partner_status' => $partnerStatus,
            'project_id' => $this->project?->ulid,
            'last_message' => new MessageResource($this->whenLoaded('lastMessage')),
            'last_message_at' => $lastMessageAt instanceof \DateTimeInterface ? $lastMessageAt->format(\DateTimeInterface::ATOM) : (is_string($lastMessageAt) ? $lastMessageAt : null),
            'unread_count' => $unreadCount,
            'participants_count' => $this->whenCounted('participants'),
            'participants' => UserResource::collection($this->whenLoaded('participants')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
