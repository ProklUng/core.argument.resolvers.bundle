<?php

namespace Prokl\CustomArgumentResolverBundle\Event\Listeners;

use Prokl\CustomArgumentResolverBundle\Event\Exceptions\AnonymousDenyAccessException;
use Prokl\CustomArgumentResolverBundle\Event\Exceptions\UserDenyAccessException;
use Prokl\CustomArgumentResolverBundle\Event\Interfaces\OnControllerRequestHandlerInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * Class UserPermissions
 * @package Prokl\CustomArgumentResolverBundle\Event\Listeners
 *
 * @since 18.02.2021
 */
class UserPermissionsWordpress implements OnControllerRequestHandlerInterface
{
    /**
     * @const string ROUTE_PARAM_NAME Параметр роута, содержащий список групп пользователей,
     * которым разрешен доступ.
     */
    private const ROUTE_PARAM_NAME = 'is_granted';

    /**
     * Обработчик события kernel.controller.
     *
     * Проверка прав на роут.
     *
     * @param ControllerEvent $event Объект события.
     *
     * @return void
     *
     * @throws AnonymousDenyAccessException Анонимным пользователям вход воспрещен.
     * @throws UserDenyAccessException      В доступе отказано по правам.
     */
    public function handle(ControllerEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $user = wp_get_current_user();
        $isGranted = $request->get(self::ROUTE_PARAM_NAME);

        // Админам можно всё.
        if (!$isGranted || current_user_can('administrator')) {
            return;
        }

        if (!$user || !$user->ID) {
            throw new AnonymousDenyAccessException(
                'Access non-authorized users denied.'
            );
        }

        $grantedGroupUsers = (array)$isGranted;

        if (array_intersect($grantedGroupUsers, (array)$user->roles)) {
            return;
        }

        throw new UserDenyAccessException(
            'Access denied.'
        );
    }
}
