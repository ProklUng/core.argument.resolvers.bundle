<?php

declare(strict_types=1);

namespace Prokl\CustomArgumentResolverBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class CustomArgumentResolver
 * @package Prokl\CustomArgumentResolver\DependencyInjection
 *
 * @since 04.12.2020
 */
class CustomArgumentResolverBundleExtension extends Extension
{
    private const DIR_CONFIG = '/../Resources/config';

    /**
     * @inheritDoc
     *
     * @return string
     */
    public function getAlias() : string
    {
        return 'custom_arguments_resolvers';
    }

    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (!$config['defaults']['enabled']) {
            return;
        }

        $container->setParameter('custom_arguments_resolvers', $config);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . self::DIR_CONFIG)
        );

        $loader->load('services.yaml');
        $loader->load('listeners.yaml');
        $loader->load('arguments_resolvers.yaml');

        if (defined('B_PROLOG_INCLUDED') && B_PROLOG_INCLUDED === true) {
            $loader->load('bitrix.yaml');
        }

        if (defined('ABSPATH')) {
            $loader->load('wordpress.yaml');
        }
    }
}
