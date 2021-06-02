<?php declare(strict_types=1);

namespace Prokl\CustomArgumentResolverBundle;

use Prokl\CustomArgumentResolverBundle\DependencyInjection\CompilerPass\RemoveServices;
use Prokl\CustomArgumentResolverBundle\DependencyInjection\CustomArgumentResolverBundleExtension;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\DependencyInjection\ControllerArgumentValueResolverPass;
use Symfony\Component\HttpKernel\DependencyInjection\RegisterControllerArgumentLocatorsPass;
use Symfony\Component\HttpKernel\DependencyInjection\RemoveEmptyControllerArgumentLocatorsPass;

/**
 * Class CustomArgumentResolver
 * @package Prokl\CustomArgumentResolverBundle
 *
 * @since 04.12.2020
 */
class CustomArgumentResolverBundle extends Bundle
{
    /**
     * @return CustomArgumentResolverBundleExtension
     */
    public function getContainerExtension(): CustomArgumentResolverBundleExtension
    {
        return new CustomArgumentResolverBundleExtension;
    }

    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container) : void
    {
        parent::build($container);

        $removeDisabledService = new RemoveServices();

        $container->addCompilerPass($removeDisabledService);
        $container->addCompilerPass(new ControllerArgumentValueResolverPass);
        $container->addCompilerPass(new RegisterControllerArgumentLocatorsPass);
        $container->addCompilerPass(new RemoveEmptyControllerArgumentLocatorsPass, PassConfig::TYPE_BEFORE_REMOVING);

    }
}
