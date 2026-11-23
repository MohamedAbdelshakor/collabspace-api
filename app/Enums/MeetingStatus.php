<?php

declare(strict_types=1);

namespace App\Enums;

enum MeetingStatus: string
{
    case Scheduled = 'scheduled';
    case Live = 'live';
    case Ended = 'ended';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Scheduled',
            self::Live => 'Live',
            self::Ended => 'Ended',
            self::Cancelled => 'Cancelled',
        };
    }
}
