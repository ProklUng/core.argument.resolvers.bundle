<?php

namespace Prokl\CustomArgumentResolverBundle\Event\Listeners\CacheRoute;

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

/**
 * Class CacheSaveResponse
 * @package Prokl\CustomArgumentResolverBundle\Event\Listeners\CacheRoute
 *
 * @since 20.07.2021
 */
final class CacheSaveResponse
{
    /**
     * @var BaseCacheResponse Utils.
     */
    private $cacherUtil;

    /**
     * CacheSaveResponse constructor.
     *
     * @param BaseCacheResponse $util Utils.
     */
    public function __construct(
        BaseCacheResponse $util
    ) {
        $this->cacherUtil = $util;
    }

    /**
     * Событие kernel.terminate.
     *
     * @param TerminateEvent $event Объект события.
     *
     * @return void
     * @throws InvalidArgumentException Когда что-то не так с кэшированием.
     */
    public function handle(TerminateEvent $event): void
    {
        if (!$event->isMasterRequest()
            ||
            $event->getResponse()->getStatusCode() === 404
        ) {
            return;
        }

        $request = $event->getRequest();

        if (!$this->cacherUtil->support($request)) {
            return;
        }

        $response = $event->getResponse();

        $keyCache = $this->cacherUtil->getCacheKey($request);
        $cacher = $this->cacherUtil->getCacher($request);

        $cacher->get(
            $keyCache,
            function ($cacheItem) use ($response) {
                return serialize($response);
            }
        );
    }
}
