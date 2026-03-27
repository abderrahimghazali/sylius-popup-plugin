<?php

declare(strict_types=1);

namespace Abderrahim\SyliusPopupPlugin\Enum;

enum TargetAudience: string
{
    case Everyone = 'everyone';
    case Guests = 'guests';
    case LoggedIn = 'logged_in';

    public function label(): string
    {
        return match ($this) {
            self::Everyone => 'Everyone',
            self::Guests => 'Guests only',
            self::LoggedIn => 'Logged-in users only',
        };
    }
}
