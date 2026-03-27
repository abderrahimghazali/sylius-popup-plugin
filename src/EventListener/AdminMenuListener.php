<?php

declare(strict_types=1);

namespace Abderrahim\SyliusPopupPlugin\EventListener;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    public function addPopupMenu(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $marketingMenu = $menu->getChild('marketing');

        if (null === $marketingMenu) {
            return;
        }

        $marketingMenu
            ->addChild('popup_campaigns', [
                'route' => 'popup_admin_popup_campaign_index',
            ])
            ->setLabel('popup.ui.popup_campaigns')
            ->setLabelAttribute('icon', 'bullhorn');
    }
}
