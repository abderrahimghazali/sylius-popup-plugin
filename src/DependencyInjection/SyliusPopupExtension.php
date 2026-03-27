<?php

declare(strict_types=1);

namespace Abderrahim\SyliusPopupPlugin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class SyliusPopupExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        // Sylius resource configuration
        $loader->load('resources/popup_campaign.yaml');

        // Sylius grid configuration
        $loader->load('grids/admin/popup_campaign.yaml');

        // Twig hooks
        $loader->load('twig_hooks/shop.yaml');

        // Framework config (rate limiter)
        $loader->load('config.yaml');
    }
}
