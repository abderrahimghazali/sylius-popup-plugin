<?php

declare(strict_types=1);

namespace Abderrahim\SyliusPopupPlugin\Service;

use Abderrahim\SyliusPopupPlugin\Entity\PopupCampaignInterface;
use Symfony\Component\HttpFoundation\Request;

interface PopupRendererInterface
{
    /**
     * @return PopupCampaignInterface[]
     */
    public function getMatchingPopups(Request $request): array;

    public function markShown(int $popupId): void;
}
