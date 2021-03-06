<?php

namespace Prokl\CustomArgumentResolverBundle\Event\Listeners;

use Prokl\CustomArgumentResolverBundle\Event\Interfaces\OnKernelRequestHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Class SetSession
 * @package Prokl\CustomArgumentResolverBundle\Event\Listeners
 *
 * @since 10.09.2020
 * @since 05.12.2020 Убрал EventSubscriberInterface, чтобы предотвратить дублирующий запуск листенера.
 */
class SetSession implements OnKernelRequestHandlerInterface
{
    use ContainerAwareTrait;

    /**
     * Событие kernel.request.
     *
     * Установить сессию Symfony для всех запросов к контроллерам.
     *
     * @param RequestEvent $event Объект события.
     *
     * @return void
     *
     * @since 10.09.2020
     */
    public function handle(RequestEvent $event): void
    {
        if (!$event->isMasterRequest()
            ||
            !$this->container->has('session.instance')
        ) {
            return;
        }

        $request = $event->getRequest();

        /** @psalm-suppress PossiblyNullArgument */
        $request->setSession($this->container->get('session.instance'));
    }
}
