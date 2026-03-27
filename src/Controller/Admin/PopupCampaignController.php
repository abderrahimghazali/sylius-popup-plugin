<?php

declare(strict_types=1);

namespace Abderrahim\SyliusPopupPlugin\Controller\Admin;

use Abderrahim\SyliusPopupPlugin\Entity\PopupCampaign;
use Abderrahim\SyliusPopupPlugin\Entity\PopupCampaignInterface;
use Abderrahim\SyliusPopupPlugin\Form\Type\PopupCampaignType;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PopupCampaignController extends AbstractController
{
    /**
     * @param FactoryInterface<PopupCampaignInterface> $popupCampaignFactory
     * @param RepositoryInterface<PopupCampaignInterface> $popupCampaignRepository
     */
    public function __construct(
        private readonly FactoryInterface $popupCampaignFactory,
        private readonly RepositoryInterface $popupCampaignRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function createAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATION_ACCESS');

        /** @var PopupCampaignInterface $campaign */
        $campaign = $this->popupCampaignFactory->createNew();

        $form = $this->createForm(PopupCampaignType::class, $campaign);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($campaign);
            $this->entityManager->flush();

            $this->addFlash('success', 'popup.flash.campaign_created');

            return $this->redirectToRoute('popup_admin_popup_campaign_index');
        }

        return $this->render('@SyliusPopupPlugin/admin/popup_campaign/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function updateAction(Request $request, int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATION_ACCESS');

        /** @var PopupCampaignInterface|null $campaign */
        $campaign = $this->popupCampaignRepository->find($id);

        if (null === $campaign) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(PopupCampaignType::class, $campaign);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'popup.flash.campaign_updated');

            return $this->redirectToRoute('popup_admin_popup_campaign_index');
        }

        return $this->render('@SyliusPopupPlugin/admin/popup_campaign/update.html.twig', [
            'form' => $form->createView(),
            'campaign' => $campaign,
        ]);
    }

    public function toggleAction(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATION_ACCESS');

        /** @var PopupCampaignInterface|null $campaign */
        $campaign = $this->popupCampaignRepository->find($id);

        if (null === $campaign) {
            throw $this->createNotFoundException();
        }

        $campaign->setEnabled(!$campaign->isEnabled());
        $this->entityManager->flush();

        return new JsonResponse(['enabled' => $campaign->isEnabled()]);
    }
}
