<?php

namespace Prokl\CustomArgumentResolverBundle\Service\Utils;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class ResolveParamsFromContainer
 * @package Prokl\CustomArgumentResolverBundle\Service\Utils
 *
 * @since 28.10.2020
 */
class ResolveParamsFromContainer
{
    use ContainerAwareTrait;

    /**
     * Разрешить все, что можно из контейнера.
     *
     * @param mixed $argItem Аргумент.
     *
     * @return mixed
     *
     */
    public function resolve($argItem)
    {
        if (!$argItem || is_object($argItem) || is_array($argItem)) {
            return $argItem;
        }

        $resolvedVariable = false;

        if (strpos($argItem, '%') === 0) {
            $containerVar = str_replace('%', '', $argItem);

            // Есть такой параметр в контейнере - действуем.
            if ($this->container->hasParameter($containerVar)) {
                $resolvedVarValue = $this->container->getParameter($containerVar);
                $resolvedVariable = true;

                if (is_string($resolvedVarValue) && $this->container->has($resolvedVarValue)) {
                    $resolvedVarValue = '@' . $resolvedVarValue;
                }

                $argItem = $resolvedVarValue;
            }

            // Продолжаем дальше, потому что в переменной может быть алиас сервиса.
        }

        // Если использован алиас сервиса, то попробовать получить его из контейнера.
        if (is_string($argItem) && strpos($argItem, '@') === 0) {
            $resolvedService = $this->container->get(ltrim($argItem, '@'));

            if ($resolvedService !== null) {
                return $resolvedService;
            }
        }

        return !$resolvedVariable ? null : $argItem;
    }
}
