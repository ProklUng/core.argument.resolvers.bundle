<?php

namespace Prokl\CustomArgumentResolverBundle\Tests\Cases\Listeners\CacheRoute;

use Mockery;
use Prokl\CustomArgumentResolverBundle\Event\Listeners\CacheRoute\BaseCacheResponse;
use Prokl\CustomArgumentResolverBundle\Event\Listeners\CacheRoute\CacheSaveResponse;
use Prokl\CustomArgumentResolverBundle\Tests\Tools\BaseTestCase;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Class BaseCacheResponseTest
 * @package Prokl\CustomArgumentResolverBundle\Tests\Cases\Listeners\CacheRoute
 * @coversDefaultClass CacheSaveResponse
 *
 * @since 21.07.2021
 */
class CacheSaveResponseTest extends BaseTestCase
{
    /**
     * @var CacheSaveResponse $obTestObject Тестируемый объект.
     */
    protected $obTestObject;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->obTestObject = new CacheSaveResponse(
            $this->getMockBaseCacheResponse()
        );
    }

    /**
     * handle().
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function testHandle() : void
    {
        $response = new Response();
        $event = new TerminateEvent(
            $this->getMockHttpKernelInterface(),
            $this->getFakeRequest(true, 'GET'),
            $response
        );

        $this->obTestObject->handle($event);

        $event->getResponse();

        // Тестирование идет на количество вызовов.
        $this->assertTrue(true);
    }

    private function getMockHttpKernelInterface()
    {
        $mock = Mockery::mock(HttpKernelInterface::class);

        return $mock;
    }

    /**
     * Мок BaseCacheResponse.
     *
     * @return mixed
     */
    private function getMockBaseCacheResponse()
    {
        $mock = Mockery::mock(BaseCacheResponse::class);

        $mock = $mock->shouldReceive('support')->once()->andReturn(true);
        $mock = $mock->shouldReceive('getCacher')->once()->andReturn($this->getMockCacheItemInterface(['OK']));
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
    private function getMockCacheItemInterface(array $return)
    {
        $mock = Mockery::mock(CacheInterface::class);
        $mock = $mock->shouldReceive('get')->andReturn($return)
            ->shouldReceive('delete')->never()
        ;

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
