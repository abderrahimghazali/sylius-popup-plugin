<?php

declare(strict_types=1);

namespace Abderrahim\SyliusPopupPlugin\Twig;

use Abderrahim\SyliusPopupPlugin\Service\PopupRendererInterface;
use Symfony\Component\HttpFoundation\Request;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class PopupRendererExtension extends AbstractExtension
{
    public function __construct(
        private readonly PopupRendererInterface $popupRenderer,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('popup_renderer_get_popups', [$this, 'getPopups']),
        ];
    }

    public function getPopups(Request $request): array
    {
        return $this->popupRenderer->getMatchingPopups($request);
    }
}
