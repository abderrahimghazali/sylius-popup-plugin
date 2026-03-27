<?php

declare(strict_types=1);

namespace Abderrahim\SyliusPopupPlugin\Repository;

use Abderrahim\SyliusPopupPlugin\Entity\PopupCampaignInterface;
use Abderrahim\SyliusPopupPlugin\Enum\TargetAudience;
use Abderrahim\SyliusPopupPlugin\Enum\TargetPages;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;

/**
 * @extends RepositoryInterface<PopupCampaignInterface>
 */
interface PopupCampaignRepositoryInterface extends RepositoryInterface
{
    /**
     * @return PopupCampaignInterface[]
     */
    public function findEnabledByTargeting(TargetPages $targetPages, TargetAudience $targetAudience): array;
}
