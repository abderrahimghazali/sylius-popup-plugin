<?php

declare(strict_types=1);

namespace Abderrahim\SyliusPopupPlugin\Enum;

enum ShowFrequency: string
{
    case Session = 'session';
    case Day = 'day';
    case Week = 'week';

    public function label(): string
    {
        return match ($this) {
            self::Session => 'Once per session',
            self::Day => 'Once per 24 hours',
            self::Week => 'Once per 7 days',
        };
    }
}
