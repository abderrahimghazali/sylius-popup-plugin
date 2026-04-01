<?php

declare(strict_types=1);

namespace Abderrahim\SyliusPopupPlugin\Tests\Unit\Service;

use Abderrahim\SyliusPopupPlugin\Entity\PopupCampaign;
use Abderrahim\SyliusPopupPlugin\Enum\TargetAudience;
use Abderrahim\SyliusPopupPlugin\Enum\TargetPages;
use Abderrahim\SyliusPopupPlugin\Repository\PopupCampaignRepositoryInterface;
use Abderrahim\SyliusPopupPlugin\Service\PopupRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class PopupRendererTest extends TestCase
{
    private PopupCampaignRepositoryInterface&MockObject $repository;
    private Security&MockObject $security;
    private RequestStack&MockObject $requestStack;
    private SessionInterface&MockObject $session;
    private PopupRenderer $renderer;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(PopupCampaignRepositoryInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->requestStack->method('getSession')->willReturn($this->session);
        $this->session->method('get')->with('_popup_shown', [])->willReturn([]);

        $this->renderer = new PopupRenderer($this->repository, $this->security, $this->requestStack);
    }

    public function testItReturnsMatchingPopupsForGuestOnProductPage(): void
    {
        $this->security->method('getUser')->willReturn(null);

        $campaign = new PopupCampaign();
        $this->repository
            ->method('findEnabledByTargeting')
            ->with(TargetPages::Product, TargetAudience::Guests)
            ->willReturn([$campaign]);

        $request = Request::create('/en_US/products/t-shirt');
        $request->attributes->set('_route', 'sylius_shop_product_show');

        $result = $this->renderer->getMatchingPopups($request);

        $this->assertCount(1, $result);
        $this->assertSame($campaign, $result[0]);
    }

    public function testItResolvesCartPage(): void
    {
        $this->security->method('getUser')->willReturn(null);

        $this->repository
            ->expects($this->once())
            ->method('findEnabledByTargeting')
            ->with(TargetPages::Cart, TargetAudience::Guests)
            ->willReturn([]);

        $request = Request::create('/en_US/cart');
        $request->attributes->set('_route', 'sylius_shop_cart_summary');

        $this->renderer->getMatchingPopups($request);
    }

    public function testItResolvesCheckoutPage(): void
    {
        $this->security->method('getUser')->willReturn(null);

        $this->repository
            ->expects($this->once())
            ->method('findEnabledByTargeting')
            ->with(TargetPages::Checkout, TargetAudience::Guests)
            ->willReturn([]);

        $request = Request::create('/en_US/checkout/address');
        $request->attributes->set('_route', 'sylius_shop_checkout_address');

        $this->renderer->getMatchingPopups($request);
    }

    public function testItResolvesAllPagesForGenericRoutes(): void
    {
        $this->security->method('getUser')->willReturn(null);

        $this->repository
            ->expects($this->once())
            ->method('findEnabledByTargeting')
            ->with(TargetPages::All, TargetAudience::Guests)
            ->willReturn([]);

        $request = Request::create('/en_US/');
        $request->attributes->set('_route', 'sylius_shop_homepage');

        $this->renderer->getMatchingPopups($request);
    }

    public function testItResolvesLoggedInAudience(): void
    {
        $user = $this->createMock(\Symfony\Component\Security\Core\User\UserInterface::class);
        $this->security->method('getUser')->willReturn($user);

        $this->repository
            ->expects($this->once())
            ->method('findEnabledByTargeting')
            ->with(TargetPages::All, TargetAudience::LoggedIn)
            ->willReturn([]);

        $request = Request::create('/en_US/');
        $request->attributes->set('_route', 'sylius_shop_homepage');

        $this->renderer->getMatchingPopups($request);
    }
}
