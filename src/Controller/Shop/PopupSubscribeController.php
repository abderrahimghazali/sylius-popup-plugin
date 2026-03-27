<?php

declare(strict_types=1);

namespace Abderrahim\SyliusPopupPlugin\Controller\Shop;

use Abderrahim\SyliusPopupPlugin\Entity\PopupCampaignInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PopupSubscribeController
{
    /**
     * @param RepositoryInterface<PopupCampaignInterface> $popupCampaignRepository
     */
    public function __construct(
        private readonly RepositoryInterface $popupCampaignRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ValidatorInterface $validator,
        private readonly RateLimiterFactory $popupSubscribeLimiter,
    ) {
    }

    public function __invoke(Request $request, int $id): Response
    {
        // Rate limiting
        $limiter = $this->popupSubscribeLimiter->create($request->getClientIp() ?? 'unknown');
        if (false === $limiter->consume()->isAccepted()) {
            return new JsonResponse(
                ['error' => 'Too many requests. Please try again later.'],
                Response::HTTP_TOO_MANY_REQUESTS,
            );
        }

        /** @var PopupCampaignInterface|null $campaign */
        $campaign = $this->popupCampaignRepository->find($id);

        if (null === $campaign || !$campaign->isEnabled()) {
            return new JsonResponse(
                ['error' => 'Popup campaign not found.'],
                Response::HTTP_NOT_FOUND,
            );
        }

        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? '';

        $violations = $this->validator->validate($email, [
            new NotBlank(),
            new Email(),
        ]);

        if ($violations->count() > 0) {
            return new JsonResponse(
                ['error' => 'Invalid email address.'],
                Response::HTTP_BAD_REQUEST,
            );
        }

        $this->eventDispatcher->dispatch(
            new GenericEvent($campaign, [
                'email' => $email,
                'popupId' => $campaign->getId(),
            ]),
            'sylius_popup.email_captured',
        );

        return new JsonResponse(['success' => true]);
    }
}
