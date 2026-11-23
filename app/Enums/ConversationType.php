<?php

declare(strict_types=1);

namespace App\Enums;

enum ConversationType: string
{
    case Direct = 'direct';
    case Channel = 'channel';

    public function isDirect(): bool
    {
        return $this === self::Direct;
    }

    public function isChannel(): bool
    {
        return $this === self::Channel;
    }
}
