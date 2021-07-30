<?php

namespace Prokl\CustomArgumentResolverBundle\Event\InjectorController;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * Interface InjectorControllerInterface
 * Интерфейс инжекции параметров в контроллеры.
 * @package Prokl\CustomArgumentResolverBundle\Event\InjectorController
 * @codeCoverageIgnore
 *
 * @since 08.10.2020 Сеттер контейнера.
 * @since 30.07.2021 Move to ControllerArgumentsEvent.
 */
interface InjectorControllerInterface
{
    /**
     * Инжекция аргументов в контроллер.
     *
     * @param ControllerArgumentsEvent $event Событие.
     *
     * @return ControllerArgumentsEvent
     */
    public function inject(ControllerArgumentsEvent $event) : ControllerArgumentsEvent;

    /**
     * Сеттер сервис-контейнера.
     *
     * @param ContainerInterface|null $container Контейнер.
     *
     * @return void
     */
    public function setContainer(ContainerInterface $container = null);
}
