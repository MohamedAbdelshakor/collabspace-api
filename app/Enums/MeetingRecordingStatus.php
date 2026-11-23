<?php

declare(strict_types=1);

namespace App\Enums;

enum MeetingRecordingStatus: string
{
    case None = 'none';
    case Processing = 'processing';
    case Ready = 'ready';
    case Failed = 'failed';
}
