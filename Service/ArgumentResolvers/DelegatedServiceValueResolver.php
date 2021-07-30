<?php

namespace Prokl\CustomArgumentResolverBundle\Service\ArgumentResolvers;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Class DelegatedServiceValueResolver
 * @package Prokl\CustomArgumentResolverBundle\Service\ArgumentResolvers
 *
 * @since 20.07.2021
 */
final class DelegatedServiceValueResolver implements ArgumentValueResolverInterface
{
    /**
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * @var array $delegatedContainers Делегированные контейнеры.
     */
    private $delegatedContainers = [];

    /**
     * DelegatedServiceValueResolver constructor.
     *
     * @param ContainerInterface $container Контейнер.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param mixed $delegatedContainers Делегированные контейнеры.
     *
     * @return $this
     */
    public function setDelegatedContainers($delegatedContainers): self
    {
        $iterator = $delegatedContainers->getIterator();
        $array = iterator_to_array($iterator);
        $this->delegatedContainers = $array;

        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        $controller = $request->attributes->get('_controller');

        if (\is_array($controller) && \is_callable($controller, true) && \is_string($controller[0])) {
            $controller = $controller[0].'::'.$controller[1];
        } elseif (!\is_string($controller) || '' === $controller) {
            return false;
        }

        if ('\\' === $controller[0]) {
            $controller = ltrim($controller, '\\');
        }

        $controllerClass = '';
        if (is_string($controller)) {
            list($controllerClass) = explode('::', $controller);
        }

        if (is_array($controller)) {
            $controllerClass = $controller[0];
        }

        if ($this->container->has($controllerClass)) {
            try {
                $nameAttribute = $this->getNameAttribute($request, $argument->getName());

                if ($nameAttribute) {
                    $this->checkServiceInDelegateContainer($nameAttribute);
                    return true;
                }
            } catch (ServiceNotFoundException $e) {
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        try {
            $nameAttribute = $this->getNameAttribute($request, $argument->getName());
            if ($nameAttribute) {
                yield $this->checkServiceInDelegateContainer($nameAttribute);
            }
        } catch (ServiceNotFoundException $e) {
        }

        yield null;
    }

    /**
     * @param Request $request Request.
     * @param string  $name    Название параметра в секции _defaults.
     *
     * @return string
     */
    private function getNameAttribute(Request $request, string $name) : string
    {
        $nameAttribute = $request->attributes->get($name);
        if (!$nameAttribute) {
            $nameAttribute = $request->attributes->get('$' . $name);
            return ltrim($nameAttribute, '@');
        }

        return ltrim($nameAttribute, '@');
    }

    /**
     * Проверить сервис в делегированных контейнерах.
     *
     * @param string $serviceId ID сервиса.
     *
     * @return mixed
     * @throws ServiceNotFoundException Когда сервис не найден.
     */
    private function checkServiceInDelegateContainer(string $serviceId)
    {
        foreach ($this->delegatedContainers as $container) {
            if ($container->has($serviceId)) {
                return $container->get($serviceId);
            }
        }

        throw new ServiceNotFoundException(
            $serviceId . ' not found in main or delegated containers.'
        );
    }
}
