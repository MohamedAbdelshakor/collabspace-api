<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\LinkPreview;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LinkPreview
 */
final class LinkPreviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'url' => $this->url,
            'title' => $this->title,
            'description' => $this->description,
            'image_url' => $this->image_url,
        ];
    }
}
