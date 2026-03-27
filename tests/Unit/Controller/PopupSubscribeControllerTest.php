<?php

declare(strict_types=1);

namespace Abderrahim\SyliusPopupPlugin\Tests\Unit\Controller;

use Abderrahim\SyliusPopupPlugin\Controller\Shop\PopupSubscribeController;
use Abderrahim\SyliusPopupPlugin\Entity\PopupCampaign;
use Abderrahim\SyliusPopupPlugin\Entity\PopupCampaignInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\SlidingWindowLimiter;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PopupSubscribeControllerTest extends TestCase
{
    /** @var RepositoryInterface<PopupCampaignInterface>&MockObject */
    private RepositoryInterface&MockObject $repository;
    private EventDispatcherInterface&MockObject $eventDispatcher;
    private ValidatorInterface&MockObject $validator;
    private RateLimiterFactory&MockObject $limiterFactory;
    private PopupSubscribeController $controller;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(RepositoryInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->limiterFactory = $this->createMock(RateLimiterFactory::class);

        $this->controller = new PopupSubscribeController(
            $this->repository,
            $this->eventDispatcher,
            $this->validator,
            $this->limiterFactory,
        );
    }

    public function testItReturns404WhenCampaignNotFound(): void
    {
        $this->setupRateLimiter(true);
        $this->repository->method('find')->willReturn(null);

        $request = $this->createJsonRequest(['email' => 'test@example.com']);
        $response = ($this->controller)($request, 999);

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testItReturns400ForInvalidEmail(): void
    {
        $this->setupRateLimiter(true);

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

    private function createJsonRequest(array $data): Request
    {
        $request = Request::create('/api/v2/shop/popup/1/subscribe', 'POST', [], [], [], [], json_encode($data));
        $request->headers->set('Content-Type', 'application/json');

        return $request;
    }

    private function setupRateLimiter(bool $accepted): void
    {
        $rateLimit = $this->createMock(RateLimit::class);
        $rateLimit->method('isAccepted')->willReturn($accepted);

        $limiter = $this->createMock(SlidingWindowLimiter::class);
        $limiter->method('consume')->willReturn($rateLimit);

        $this->limiterFactory->method('create')->willReturn($limiter);
    }
}
