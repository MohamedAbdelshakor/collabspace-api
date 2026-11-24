<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\LinkPreview;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Message
 */
final class MessageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $linkPreviews = [];
        if (! empty($this->body) && preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $this->body, $matches)) {
            $urls = array_unique($matches[0]);
            $hashes = array_map(fn (string $u): string => hash('sha256', $u), $urls);
            $previews = LinkPreview::whereIn('url_hash', $hashes)->get();
            $linkPreviews = LinkPreviewResource::collection($previews);
        }

        $createdAt = $this->created_at;
        $editedAt = $this->edited_at;

        return [
            'id' => $this->ulid,
            'conversation_id' => $this->conversation?->ulid,
            'body' => $this->body,
            'sender' => new UserResource($this->whenLoaded('user')),
            'parent_id' => $this->parent?->ulid,
            'replies_count' => $this->replies_count,
            'link_previews' => $linkPreviews,
            'created_at' => $createdAt instanceof \DateTimeInterface ? $createdAt->format(\DateTimeInterface::ATOM) : (is_string($createdAt) ? $createdAt : null),
            'edited_at' => $editedAt instanceof \DateTimeInterface ? $editedAt->format(\DateTimeInterface::ATOM) : (is_string($editedAt) ? $editedAt : null),
        ];
    }
}
