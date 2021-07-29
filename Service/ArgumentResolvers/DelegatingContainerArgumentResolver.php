<?php

namespace Prokl\CustomArgumentResolverBundle\Service\ArgumentResolvers;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Class DelegatingContainerArgumentResolver
 * @package Prokl\CustomArgumentResolverBundle\Service\ArgumentResolvers
 *
 * @since 29.07.2021
 */
class DelegatingContainerArgumentResolver implements ArgumentValueResolverInterface
{
    /**
     * @var ContainerInterface[] $delegatingContainers Делегированные контейнеры.
     */
    private $delegatingContainers = [];

    /**
     * DelegatingContainerArgumentResolver constructor.
     *
     * @param mixed $delegatingContainers Список делегированных контейнеров.
     * Тэг - delegated.container.
     */
    public function __construct(...$delegatingContainers)
    {
        foreach ($delegatingContainers as $container) {
            $iterator = $container->getIterator();
            $array = iterator_to_array($iterator);
            $this->delegatingContainers[] = $array;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        foreach ($this->delegatingContainers as $delegatingContainer) {
            if (!$delegatingContainer[0]) {
                continue;
            }

            $class = $argument->getType();

            if ($delegatingContainer[0]->has($class)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        foreach ($this->delegatingContainers as $delegatingContainer) {
            if ($delegatingContainer[0]->has($argument->getType())) {
                return yield $delegatingContainer[0]->get($argument->getType());
            }
        }

        yield null;
    }
}
