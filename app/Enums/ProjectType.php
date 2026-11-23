<?php

declare(strict_types=1);

namespace App\Enums;

enum ProjectType: string
{
    case SaaS = 'saas';
    case WebApp = 'web_app';
    case MobileApp = 'mobile_app';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::SaaS => 'SaaS',
            self::WebApp => 'Web Platform',
            self::MobileApp => 'Mobile App',
            self::Other => 'Other',
        };
    }
}
