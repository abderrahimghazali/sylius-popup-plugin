<?php

declare(strict_types=1);

namespace Abderrahim\SyliusPopupPlugin\Service;

use Abderrahim\SyliusPopupPlugin\Entity\PopupCampaignInterface;
use Abderrahim\SyliusPopupPlugin\Enum\ShowFrequency;
use Abderrahim\SyliusPopupPlugin\Enum\TargetAudience;
use Abderrahim\SyliusPopupPlugin\Enum\TargetPages;
use Abderrahim\SyliusPopupPlugin\Repository\PopupCampaignRepositoryInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class PopupRenderer
{
    public function __construct(
        private readonly PopupCampaignRepositoryInterface $popupCampaignRepository,
        private readonly Security $security,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * @return PopupCampaignInterface[]
     */
    public function getMatchingPopups(Request $request): array
    {
        $targetPages = $this->resolveTargetPages($request);
        $targetAudience = $this->resolveTargetAudience();

        $popups = $this->popupCampaignRepository->findEnabledByTargeting($targetPages, $targetAudience);

        return array_values(array_filter($popups, fn (PopupCampaignInterface $popup) => !$this->wasRecentlyShown($popup)));
    }

    private function wasRecentlyShown(PopupCampaignInterface $popup): bool
    {
        $session = $this->requestStack->getSession();
        /** @var array<int, int> $shownPopups */
        $shownPopups = $session->get('_popup_shown', []);
        $popupId = $popup->getId();

        if (null === $popupId || !isset($shownPopups[$popupId])) {
            return false;
        }

        $shownAt = $shownPopups[$popupId];
        $now = time();

        return match ($popup->getShowFrequency()) {
            ShowFrequency::Session => true,
            ShowFrequency::Day => ($now - $shownAt) < 86400,
            ShowFrequency::Week => ($now - $shownAt) < 604800,
        };
    }

    public function markShown(int $popupId): void
    {
        $session = $this->requestStack->getSession();
        /** @var array<int, int> $shownPopups */
        $shownPopups = $session->get('_popup_shown', []);
        $shownPopups[$popupId] = time();
        $session->set('_popup_shown', $shownPopups);
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
