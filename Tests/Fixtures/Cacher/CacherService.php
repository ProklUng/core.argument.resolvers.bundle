<?php

namespace Prokl\CustomArgumentResolverBundle\Tests\Fixtures\Cacher;

use Symfony\Contracts\Cache\CacheInterface;

/**
 * Class CacherService
 * @package Prokl\CustomArgumentResolverBundle\Tests\Fixtures\Cacher
 *
 * @since 21.07.2021
 */
class CacherService implements CacheInterface
{
    /**
     * @inheritdoc
     */
    public function get(string $key, callable $callback, float $beta = null, array &$metadata = null)
    {
       return 'OK';
    }

    /**
     * @inheritdoc
     */
    public function delete(string $key): bool
    {
        return true;
    }
}