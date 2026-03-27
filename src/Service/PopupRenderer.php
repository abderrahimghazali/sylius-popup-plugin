<?php

declare(strict_types=1);

namespace Abderrahim\SyliusPopupPlugin\Service;

use Abderrahim\SyliusPopupPlugin\Entity\PopupCampaignInterface;
use Abderrahim\SyliusPopupPlugin\Enum\TargetAudience;
use Abderrahim\SyliusPopupPlugin\Enum\TargetPages;
use Abderrahim\SyliusPopupPlugin\Repository\PopupCampaignRepositoryInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

final class PopupRenderer
{
    public function __construct(
        private readonly PopupCampaignRepositoryInterface $popupCampaignRepository,
        private readonly Security $security,
    ) {
    }

    /**
     * @return PopupCampaignInterface[]
     */
    public function getMatchingPopups(Request $request): array
    {
        $targetPages = $this->resolveTargetPages($request);
        $targetAudience = $this->resolveTargetAudience();

        return $this->popupCampaignRepository->findEnabledByTargeting($targetPages, $targetAudience);
    }

    private function resolveTargetPages(Request $request): TargetPages
    {
        $route = $request->attributes->get('_route', '');

        return match (true) {
            str_contains($route, 'product_show') || str_contains($route, 'product_index') => TargetPages::Product,
            str_contains($route, 'cart') => TargetPages::Cart,
            str_contains($route, 'checkout') => TargetPages::Checkout,
            default => TargetPages::All,
        };
    }

    private function resolveTargetAudience(): TargetAudience
    {
        if (null === $this->security->getUser()) {
            return TargetAudience::Guests;
        }

        return TargetAudience::LoggedIn;
    }
}
