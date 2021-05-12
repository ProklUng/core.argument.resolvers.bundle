<?php

namespace Prokl\CustomArgumentResolverBundle\Event\Listeners;

use Exception;
use Prokl\CustomArgumentResolverBundle\Event\Exceptions\InvalidAjaxCallException;
use Prokl\CustomArgumentResolverBundle\Event\Interfaces\OnControllerRequestHandlerInterface;
use Prokl\CustomArgumentResolverBundle\Event\Traits\UseTraitChecker;
use Prokl\CustomArgumentResolverBundle\Event\Traits\ValidatorTraits\SecurityAjaxCallTrait;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * Class AjaxCall
 * @package Prokl\CustomArgumentResolverBundle\Event\Listeners
 *
 * @since 10.09.2020
 * @since 11.09.2020 Упрощение.
 * @since 05.12.2020 Убрал EventSubscriberInterface, чтобы предотвратить дублирующий запуск листенера.
 */
class AjaxCall implements OnControllerRequestHandlerInterface
{
    use UseTraitChecker;

    /**
     * Обработчик события kernel.controller.
     *
     * Проверка на вызов AJAX.
     *
     * @param ControllerEvent $event Объект события.
     *
     * @return void
     *
     * @throws InvalidAjaxCallException Вызов не AJAX.
     */
    public function handle(ControllerEvent $event): void
    {
        if (!$event->isMasterRequest() || !$this->useTrait($event, SecurityAjaxCallTrait::class)) {
            return;
        }

        if (!$event->getRequest()->isXmlHttpRequest()) {
            throw new InvalidAjaxCallException('Invalid type call.');
        }
    }
}