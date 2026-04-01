<?php

declare(strict_types=1);

namespace Abderrahim\SyliusPopupPlugin\Repository;

use Abderrahim\SyliusPopupPlugin\Entity\PopupCampaignInterface;
use Abderrahim\SyliusPopupPlugin\Enum\TargetAudience;
use Abderrahim\SyliusPopupPlugin\Enum\TargetPages;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class PopupCampaignRepository extends EntityRepository implements PopupCampaignRepositoryInterface
{
    /**
     * @return PopupCampaignInterface[]
     */
    public function findEnabledByTargeting(TargetPages $targetPages, TargetAudience $targetAudience): array
    {
        $qb = $this->createQueryBuilder('pc')
            ->andWhere('pc.enabled = :enabled')
            ->setParameter('enabled', true)
            ->orderBy('pc.priority', 'DESC');

        // Match exact target page or 'all'
        $qb->andWhere('pc.targetPages = :targetPages OR pc.targetPages = :allPages')
            ->setParameter('targetPages', $targetPages->value)
            ->setParameter('allPages', TargetPages::All->value);

        // Match exact audience or 'everyone'
        $qb->andWhere('pc.targetAudience = :targetAudience OR pc.targetAudience = :everyone')
            ->setParameter('targetAudience', $targetAudience->value)
            ->setParameter('everyone', TargetAudience::Everyone->value);

        // Limit to prevent popup flooding
        $qb->setMaxResults(3);

        return $qb->getQuery()->getResult();
    }
}
