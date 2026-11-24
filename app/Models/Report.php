<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReportStatus;
use App\Enums\ReportType;
use App\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property ReportType $type
 * @property ReportStatus $status
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property array<string, mixed>|null $data
 * @property string|null $ai_summary
 * @property string|null $csv_path
 * @property string|null $pdf_path
 * @property string|null $note
 * @property string|null $failed_reason
 */
class Report extends Model
{
    use HasFactory, HasUlid;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'note',
        'start_date',
        'end_date',
        'status',
        'data',
        'ai_summary',
        'csv_path',
        'pdf_path',
        'failed_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ReportType::class,
            'status' => ReportStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'data' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
