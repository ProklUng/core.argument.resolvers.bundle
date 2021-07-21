<?php

namespace Prokl\CustomArgumentResolverBundle\Event\Listeners\CacheRoute;

use RuntimeException;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Class CacheLoadResponse
 * @package Prokl\CustomArgumentResolverBundle\Event\Listeners\CacheRoute
 *
 * @since 20.07.2021
 */
final class CacheLoadResponse
{
    /**
     * @var BaseCacheResponse Utils.
     */
    private $cacherUtil;

    /**
     * CacheLoadResponse constructor.
     *
     * @param BaseCacheResponse $util Utils.
     */
    public function __construct(
        BaseCacheResponse $util
    ) {
        $this->cacherUtil = $util;
    }

    /**
     * Событие kernel.request.
     *
     * @param RequestEvent $event Объект события.
     *
     * @return void
     */
    public function handle(RequestEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$this->cacherUtil->support($request)) {
            return;
        }

        $keyCache = $this->cacherUtil->getCacheKey($request);
        $cacher = $this->cacherUtil->getCacher($request);

        if ($cacher->hasItem($keyCache)) {
            /** @var CacheItem|null $item */
            $item = $cacher->getItem($keyCache);
            if ($item !== null) {
                /** @var Response|false $data */
                $data = unserialize($item->get());
                if (!$data) {
                    throw new RuntimeException(
                        sprintf(
                            'Error deserialize Response from cache. Route %s.',
                            $request->attributes->get('_route')
                        )
                    );
                }

                // Пометить, что Response восстановлен из кэша.
                $data->headers->set('x-cached-response', true);
                $event->setResponse($data);
            }
        }
    }
}
