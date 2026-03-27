<?php

declare(strict_types=1);

namespace Abderrahim\SyliusPopupPlugin\Enum;

enum PopupStyle: string
{
    case Modal = 'modal';
    case Bar = 'bar';

    public function label(): string
    {
        return match ($this) {
            self::Modal => 'Centered Modal',
            self::Bar => 'Bottom Bar',
        };
    }
}
