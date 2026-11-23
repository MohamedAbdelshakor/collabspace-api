<?php

declare(strict_types=1);

namespace App\Enums;

enum AvailabilityStatus: string
{
    case Available = 'available';
    case Busy = 'busy';
    case Away = 'away';
    case OnLeave = 'on_leave';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Busy => 'Busy',
            self::Away => 'Away',
            self::OnLeave => 'On Leave',
        };
    }

    public function isAssignable(): bool
    {
        return $this !== self::OnLeave;
    }
}
