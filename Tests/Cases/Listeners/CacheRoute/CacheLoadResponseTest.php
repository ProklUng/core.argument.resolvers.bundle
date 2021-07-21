<?php

namespace Prokl\CustomArgumentResolverBundle\Tests\Cases\Listeners\CacheRoute;

use DG\BypassFinals;
use Mockery;
use Prokl\CustomArgumentResolverBundle\Event\Listeners\CacheRoute\BaseCacheResponse;
use Prokl\CustomArgumentResolverBundle\Event\Listeners\CacheRoute\CacheLoadResponse;
use Prokl\CustomArgumentResolverBundle\Tests\Tools\BaseTestCase;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Class CacheLoadResponseTest
 * @package Prokl\CustomArgumentResolverBundle\Tests\Cases\Listeners\CacheRoute
 * @coversDefaultClass CacheLoadResponse
 *
 * @since 21.07.2021
 */
class CacheLoadResponseTest extends BaseTestCase
{
    /**
     * @var CacheLoadResponse $obTestObject Тестируемый объект.
     */
    protected $obTestObject;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        BypassFinals::enable();

        parent::setUp();

    }

    /**
     * handle(). Response нет в кэше.
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function testHandleNoHitCache() : void
    {
        $this->obTestObject = new CacheLoadResponse(
            $this->getMockBaseCacheResponse(false)
        );

        $event = new RequestEvent(
            $this->getMockHttpKernelInterface(),
            $this->getFakeRequest(true, 'GET'),
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->obTestObject->handle($event);

        $resultResponse = $event->getResponse();

        $this->assertNull($resultResponse);
    }

    /**
     * @return mixed
     */
    private function getMockHttpKernelInterface()
    {
        return Mockery::mock(HttpKernelInterface::class);
    }

    /**
     * Мок BaseCacheResponse.
     *
     * @param boolean $has
     *
     * @return mixed
     */
    private function getMockBaseCacheResponse(bool $has)
    {
        $mock = Mockery::mock(BaseCacheResponse::class);

        $mock = $mock->shouldReceive('support')->once()->andReturn(true);
        $mock = $mock->shouldReceive('getCacher')->once()->andReturn($this->getMockCacheItemInterface(['OK'], $has));
        $mock = $mock->shouldReceive('getCacheKey')->once()->andReturn('1234567');

        return $mock->getMock();
    }

    /**
     * Мок CacheInterface.
     *
     * @param array $return
     *
     * @return mixed
     */
    private function getMockCacheItemInterface(array $return, bool $has)
    {
        $mockCacheItem = Mockery::mock(CacheItem::class);
        $mockCacheItem = $mockCacheItem->shouldReceive('get')->andReturn(serialize(new Response()));

        $mock = Mockery::mock(CacheInterface::class);
        $mock = $mock->shouldReceive('get')->andReturn($return)
            ->shouldReceive('delete')->never();

        $mock = $mock->shouldReceive('hasItem')->andReturn($has);
        $mock = $mock->shouldReceive('getItem')->andReturn($mockCacheItem);

        return $mock->getMock();
    }

    /**
     * Request.
     *
     * @param boolean $value  Значение _cacheble.
     * @param string  $method Тип запроса.
     *
     * @return Request
     */
    private function getFakeRequest(bool $value, string $method) : Request
    {
        $request = new Request();

        $request->attributes->set('_cacheble', $value);
        $request->setMethod($method);

        return $request;
    }
}
