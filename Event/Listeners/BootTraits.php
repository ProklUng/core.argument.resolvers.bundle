<?php

namespace Prokl\CustomArgumentResolverBundle\Event\Listeners;

use Prokl\CustomArgumentResolverBundle\Event\Interfaces\OnControllerRequestHandlerInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * Class BootTraits
 * Bootable Traits.
 * @package Prokl\CustomArgumentResolverBundle\Event\Listeners
 *
 * @since 10.09.2020
 * @since 11.09.2020 Упрощение.
 * @since 19.09.2020 Добавлена инициализация трэйтов.
 * @since 11.10.2020 Переработка.
 * @since 05.12.2020 Убрал EventSubscriberInterface, чтобы предотвратить дублирующий запуск листенера.
 */
class BootTraits implements OnControllerRequestHandlerInterface
{
    /**
     * @var array $booted Загруженные методы трэйтов.
     */
    private $booted = [];

    /**
     * Обработчик события kernel.controller.
     *
     * Инициализация трэйтов контроллера. Вызов метода boot + название трэйта, если таковой существует.
     * (из Laravel)
     *
     * @param ControllerEvent $event Объект события.
     *
     * @return void
     *
     * @since 10.09.2020
     * @since 11.10.2020 Переработка.
     */
    public function handle(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (!$event->isMasterRequest()) {
            return;
        }

        $this->booted = [];

        /** @psalm-suppress InvalidArrayAccess */
        foreach ($this->classUsesRecursive($controller[0]) as $trait) {
            // Загрузка (статический метод).
            $method = 'boot' . class_basename($trait);

            /** @psalm-suppress InvalidArrayAccess */
            if ($this->methodExist($controller[0], $method)) {
                forward_static_call([$controller[0], $method]);

                $this->booted[] = $method;
            }

            // Инициализация (динамический метод).
            $method = 'initialize' . class_basename($trait);

            if ($this->methodExist($controller[0], $method)) {
                /** @psalm-suppress InvalidArrayAccess */
                $controller[0]->{$method}();

                $this->booted[] = $method;
            }
        }
    }

    /**
     * Существует ли метод и был ли он уже загружен.
     *
     * @param mixed  $class  Класс.
     * @param string $method Метод.
     *
     * @since 11.10.2020
     *
     * @return boolean
     */
    private function methodExist($class, string $method) : bool
    {
        return method_exists($class, $method)
            && !in_array($method, $this->booted, true);
    }

    /**
     * @param string|object $class
     *
     * @return array
     */
    private function classUsesRecursive($class) : array
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $results = [];

        foreach (array_reverse(class_parents($class)) + [$class => $class] as $class) {
            $results += $this->trait_uses_recursive($class);
        }

        return array_unique($results);
    }

    /**
     * Returns all traits used by a trait and its traits.
     *
     * @param string $trait
     *
     * @return array
     */
    private function trait_uses_recursive($trait) : array
    {
        $traits = class_uses($trait);

        foreach ($traits as $trait) {
            $traits += $this->trait_uses_recursive($trait);
        }

        return $traits;
    }
}
