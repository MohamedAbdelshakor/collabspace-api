<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Enums\ReportStatus;
use App\Enums\ReportType;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Report
 */
final class ReportResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $csvUrl = $this->csv_path ? Storage::disk('public')->url($this->csv_path) : null;
        $pdfUrl = $this->pdf_path ? Storage::disk('public')->url($this->pdf_path) : null;
        $type = $this->type;
        $status = $this->status;
        $startDate = $this->start_date;
        $endDate = $this->end_date;

        return [
            'id' => $this->ulid,
            'type' => $type instanceof ReportType ? $type->value : (string) $type,
            'type_label' => $type instanceof ReportType ? $type->label() : (string) $type,
            'note' => $this->note,
            'start_date' => $startDate instanceof \DateTimeInterface ? $startDate->format('Y-m-d') : (is_string($startDate) ? $startDate : null),
            'end_date' => $endDate instanceof \DateTimeInterface ? $endDate->format('Y-m-d') : (is_string($endDate) ? $endDate : null),
            'status' => $status instanceof ReportStatus ? $status->value : (string) $status,
            'ai_summary' => $this->ai_summary,
            'csv_url' => $csvUrl,
            'pdf_url' => $pdfUrl,
            'data' => $this->data,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
