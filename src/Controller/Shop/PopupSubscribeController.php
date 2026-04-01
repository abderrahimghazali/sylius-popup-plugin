<?php

declare(strict_types=1);

namespace Abderrahim\SyliusPopupPlugin\Controller\Shop;

use Abderrahim\SyliusPopupPlugin\Entity\PopupCampaignInterface;
use Abderrahim\SyliusPopupPlugin\Service\PopupRenderer;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class PopupSubscribeController
{
    private readonly LimiterInterface|RateLimiterFactory $rateLimiter;

    /**
     * @param RepositoryInterface<PopupCampaignInterface> $popupCampaignRepository
     */
    public function __construct(
        private readonly RepositoryInterface $popupCampaignRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ValidatorInterface $validator,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly CacheInterface $cache,
        private readonly PopupRenderer $popupRenderer,
        LimiterInterface|RateLimiterFactory $popupSubscribeLimiter,
    ) {
        $this->rateLimiter = $popupSubscribeLimiter;
    }

    public function __invoke(Request $request, int $id): Response
    {
        // Rate limiting
        $limiter = $this->rateLimiter instanceof RateLimiterFactory
            ? $this->rateLimiter->create($request->getClientIp() ?? 'unknown')
            : $this->rateLimiter;

        if (false === $limiter->consume()->isAccepted()) {
            return new JsonResponse(
                ['error' => 'Too many requests. Please try again later.'],
                Response::HTTP_TOO_MANY_REQUESTS,
            );
        }

        // CSRF validation
        $csrfToken = $request->headers->get('X-CSRF-Token', '');
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('popup_subscribe', $csrfToken))) {
            return new JsonResponse(
                ['error' => 'Invalid request.'],
                Response::HTTP_FORBIDDEN,
            );
        }

        $data = json_decode($request->getContent(), true);

        // Honeypot check — the "website" field must be absent or empty
        if (!empty($data['website'] ?? '')) {
            // Bot detected — return fake success to not reveal the trap
            return new JsonResponse(['success' => true]);
        }

        /** @var PopupCampaignInterface|null $campaign */
        $campaign = $this->popupCampaignRepository->find($id);

        if (null === $campaign || !$campaign->isEnabled()) {
            // Generic success to prevent campaign ID enumeration
            return new JsonResponse(['success' => true]);
        }

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

        // Duplicate email check — skip if already submitted for this campaign (24h TTL)
        $cacheKey = sprintf('popup_sub_%d_%s', $id, hash('sha256', mb_strtolower($email)));
        $isDuplicate = $this->cache->get($cacheKey, function (ItemInterface $item) {
            $item->expiresAfter(86400); // 24 hours

            return false;
        });

        if ($isDuplicate) {
            // Already subscribed — return success silently
            return new JsonResponse(['success' => true]);
        }

        // Mark as submitted
        $this->cache->delete($cacheKey);
        $this->cache->get($cacheKey, function (ItemInterface $item) {
            $item->expiresAfter(86400);

            return true;
        });

        $this->eventDispatcher->dispatch(
            new GenericEvent($campaign, [
                'email' => $email,
                'popupId' => $campaign->getId(),
            ]),
            'sylius_popup.email_captured',
        );

        // Mark popup as shown server-side
        $this->popupRenderer->markShown($id);

        return new JsonResponse(['success' => true]);
    }
}
