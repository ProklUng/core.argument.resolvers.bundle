<?php

namespace Prokl\CustomArgumentResolverBundle\Event\Listeners\CacheRoute;

use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Class CacheResponseUtil
 * @package Prokl\CustomArgumentResolverBundle\Event\Listeners\CacheRoute
 *
 * @since 20.07.2021
 */
class BaseCacheResponse
{
    use ContainerAwareTrait;

    /**
     * Экземпляр кэшера из контейнера согласно параметру.
     *
     * @param Request $request Request.
     *
     * @return CacheInterface
     * @throws RuntimeException Когда что-то не так с кэшером - не найден, не тот класс.
     */
    public function getCacher(Request $request) : CacheInterface
    {
        $cacherId = $request->attributes->get('_cacher', '');

        if (!$cacherId || !$this->container->has($cacherId)) {
            throw new RuntimeException(
                sprintf('Cacher with ID %s not exists. You dont forget set _cacher options in route file?', $cacherId)
            );
        }

        $cacher = $this->container->get($cacherId);
        if (!is_a($cacher, CacheInterface::class)) {
            throw new RuntimeException('Cacher must implementing CacheInterface.');
        }

        return $cacher;
    }

    /**
     * Подходит ли роут для кэширования.
     *
     * @param Request $request Request.
     *
     * @return boolean
     *
     * @internal Только GET запросы.
     */
    public function support(Request $request) : bool
    {
        if ($request->getMethod() !== 'GET') {
            return false;
        }

        return $request->attributes->get('_cacheble', false);
    }

    /**
     * Ключ кэша.
     *
     * @param Request $request Request.
     *
     * @return string
     */
    public function getCacheKey(Request $request): string
    {
        $data = [
            'uri' => $request->getUri(),
            'query' => $request->query->all(),
        ];

        return 'query_'.md5(serialize($data));
    }
}
