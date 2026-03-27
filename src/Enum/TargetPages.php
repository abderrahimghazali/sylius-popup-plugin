<?php

declare(strict_types=1);

namespace Abderrahim\SyliusPopupPlugin\Enum;

enum TargetPages: string
{
    case All = 'all';
    case Product = 'product';
    case Cart = 'cart';
    case Checkout = 'checkout';

    public function label(): string
    {
        return match ($this) {
            self::All => 'All pages',
            self::Product => 'Product pages only',
            self::Cart => 'Cart page only',
            self::Checkout => 'Checkout page only',
        };
    }
}
