<?php

declare(strict_types=1);

namespace Abderrahim\SyliusPopupPlugin\Enum;

enum TriggerType: string
{
    case ExitIntent = 'exit_intent';
    case TimeOnPage = 'time_on_page';
    case ScrollDepth = 'scroll_depth';
    case CartAbandonment = 'cart_abandonment';

    public function label(): string
    {
        return match ($this) {
            self::ExitIntent => 'Exit Intent',
            self::TimeOnPage => 'Time on Page',
            self::ScrollDepth => 'Scroll Depth',
            self::CartAbandonment => 'Cart Abandonment',
        };
    }
}
