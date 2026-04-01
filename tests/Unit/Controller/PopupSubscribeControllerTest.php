<?php

declare(strict_types=1);

namespace Abderrahim\SyliusPopupPlugin\Tests\Unit\Controller;

use Abderrahim\SyliusPopupPlugin\Controller\Shop\PopupSubscribeController;
use Abderrahim\SyliusPopupPlugin\Entity\PopupCampaign;
use Abderrahim\SyliusPopupPlugin\Entity\PopupCampaignInterface;
use Abderrahim\SyliusPopupPlugin\Service\PopupRendererInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class PopupSubscribeControllerTest extends TestCase
{
    /** @var RepositoryInterface<PopupCampaignInterface>&MockObject */
    private RepositoryInterface&MockObject $repository;
    private EventDispatcherInterface&MockObject $eventDispatcher;
    private ValidatorInterface&MockObject $validator;
    private CsrfTokenManagerInterface&MockObject $csrfTokenManager;
    private CacheInterface&MockObject $cache;
    private PopupRendererInterface&MockObject $popupRenderer;
    private LimiterInterface&MockObject $limiter;
    private PopupSubscribeController $controller;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(RepositoryInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->csrfTokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->popupRenderer = $this->createMock(PopupRendererInterface::class);
        $this->limiter = $this->createMock(LimiterInterface::class);

        $this->controller = new PopupSubscribeController(
            $this->repository,
            $this->eventDispatcher,
            $this->validator,
            $this->csrfTokenManager,
            $this->cache,
            $this->popupRenderer,
            $this->limiter,
        );
    }

    public function testItReturnsSuccessWhenCampaignNotFound(): void
    {
        $this->setupRateLimiter(true);
        $this->setupCsrf(true);
        $this->repository->method('find')->willReturn(null);

        $request = $this->createJsonRequest(['email' => 'test@example.com']);
        $response = ($this->controller)($request, 999);

        // Returns generic success to prevent campaign ID enumeration
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{"success":true}', $response->getContent());
    }

    public function testItReturns403ForInvalidCsrfToken(): void
    {
        $this->setupRateLimiter(true);
        $this->setupCsrf(false);

        $request = $this->createJsonRequest(['email' => 'test@example.com']);
        $response = ($this->controller)($request, 1);

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testItReturnsSuccessForHoneypotBot(): void
    {
        $this->setupRateLimiter(true);
        $this->setupCsrf(true);

        $this->eventDispatcher->expects($this->never())->method('dispatch');

        $request = $this->createJsonRequest(['email' => 'bot@spam.com', 'website' => 'http://spam.com']);
        $response = ($this->controller)($request, 1);

        // Fake success to not reveal the honeypot trap
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{"success":true}', $response->getContent());
    }

    public function testItReturns400ForInvalidEmail(): void
    {
        $this->setupRateLimiter(true);
        $this->setupCsrf(true);

        $campaign = new PopupCampaign();
        $campaign->setEnabled(true);
        $this->repository->method('find')->willReturn($campaign);

        $violation = $this->createMock(\Symfony\Component\Validator\ConstraintViolationInterface::class);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList([$violation]));

        $request = $this->createJsonRequest(['email' => 'invalid']);
        $response = ($this->controller)($request, 1);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testItReturns429WhenRateLimited(): void
    {
        $this->setupRateLimiter(false);

        $request = $this->createJsonRequest(['email' => 'test@example.com']);
        $response = ($this->controller)($request, 1);

        $this->assertSame(Response::HTTP_TOO_MANY_REQUESTS, $response->getStatusCode());
    }

    public function testItDispatchesEventOnSuccess(): void
    {
        $this->setupRateLimiter(true);
        $this->setupCsrf(true);
        $this->setupCacheNotDuplicate();

        $campaign = new PopupCampaign();
        $campaign->setEnabled(true);
        $this->repository->method('find')->willReturn($campaign);

        $this->validator->method('validate')->willReturn(new ConstraintViolationList());

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->anything(),
                'sylius_popup.email_captured',
            );

        $request = $this->createJsonRequest(['email' => 'test@example.com']);
        $response = ($this->controller)($request, 1);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{"success":true}', $response->getContent());
    }

    public function testItSkipsDuplicateEmail(): void
    {
        $this->setupRateLimiter(true);
        $this->setupCsrf(true);

        $campaign = new PopupCampaign();
        $campaign->setEnabled(true);
        $this->repository->method('find')->willReturn($campaign);

        $this->validator->method('validate')->willReturn(new ConstraintViolationList());

        // Cache returns true = already submitted
        $this->cache->method('get')->willReturn(true);

        $this->eventDispatcher->expects($this->never())->method('dispatch');

        $request = $this->createJsonRequest(['email' => 'test@example.com']);
        $response = ($this->controller)($request, 1);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{"success":true}', $response->getContent());
    }

    private function createJsonRequest(array $data): Request
    {
        $request = Request::create('/api/v2/shop/popup/1/subscribe', 'POST', [], [], [], [], json_encode($data));
        $request->headers->set('Content-Type', 'application/json');
        $request->headers->set('X-CSRF-Token', 'valid-token');

        return $request;
    }

    private function setupCsrf(bool $valid): void
    {
        $this->csrfTokenManager->method('isTokenValid')->willReturn($valid);
    }

    private function setupCacheNotDuplicate(): void
    {
        $this->cache->method('get')->willReturnCallback(function (string $key, callable $callback) {
            return $callback($this->createMock(ItemInterface::class));
        });
        $this->cache->method('delete')->willReturn(true);
    }

    private function setupRateLimiter(bool $accepted): void
    {
        $rateLimit = $this->createMock(RateLimit::class);
        $rateLimit->method('isAccepted')->willReturn($accepted);

        $this->limiter->method('consume')->willReturn($rateLimit);
    }
}
