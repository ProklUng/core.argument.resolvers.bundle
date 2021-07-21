<?php

namespace Prokl\CustomArgumentResolverBundle\Tests\Cases\Listeners\CacheRoute;

use Prokl\CustomArgumentResolverBundle\Event\Listeners\CacheRoute\BaseCacheResponse;
use Prokl\CustomArgumentResolverBundle\Tests\Fixtures\Cacher\CacherService;
use Prokl\CustomArgumentResolverBundle\Tests\Tools\BaseTestCase;
use Prokl\TestingTools\Tools\Container\BuildContainer;
use Prokl\TestingTools\Tools\PHPUnitUtils;
use ReflectionException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BaseCacheResponseTest
 * @package Prokl\CustomArgumentResolverBundle\Tests\Cases\Listeners\CacheRoute
 * @coversDefaultClass BaseCacheResponse
 *
 * @since 21.07.2021
 */
class BaseCacheResponseTest extends BaseTestCase
{
    /**
     * @var BaseCacheResponse $obTestObject Тестируемый объект.
     */
    protected $obTestObject;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $container = BuildContainer::getTestContainer(
            [
                'test_container.yaml',
            ],
            '/Tests/Fixtures/config'
        );

        parent::setUp();

        $this->obTestObject = new BaseCacheResponse();
        $this->obTestObject->setContainer($container);
    }

    /**
     * support(). Поддерживаемый запрос.
     *
     * @return void
     */
    public function testSupport() : void
    {
        $result = $this->obTestObject->support(
            $this->getFakeRequest(true, 'GET')
        );

        $this->assertTrue($result, 'Валидный Request не прошел проверку.');
    }

    /**
     * support(). Поддерживаемый запрос.
     *
     * @param string $method Тип запроса.
     *
     * @return void
     * @dataProvider dataProviderMethods
     */
    public function testSupportNotValidRequest(string $method) : void
    {
        $result = $this->obTestObject->support(
            $this->getFakeRequest(false, $method)
        );

        $this->assertFalse($result, 'Невалидный Request прошел проверку.');
    }

    /**
     * @return array
     */
    public function dataProviderMethods() : array
    {
        return [
          ['POST'],
          ['PUT'],
          ['PATCH'],
          ['DELETE'],
        ];
    }

    /**
     * getCacheKey().
     *
     * @return void
     * @throws ReflectionException
     */
    public function testGetCacheKey() : void
    {
        $baseUrl = 'http://test.loc/url/';

        $request = $this->getFakeRequest(true, 'GET');
        PHPUnitUtils::setProtectedProperty($request, 'baseUrl', $baseUrl);

        $request->query->set('testing', 'OK');

        $expected = 'query_'.md5(serialize([
                'uri' => $request->getUri(),
                'query' => $request->query->all(),
            ]));

        $result = $this->obTestObject->getCacheKey($request);

        $this->assertSame($expected, $result, 'Ключ кэша не правильный.');
    }

    /**
     * getCacher().
     *
     * @return void
     */
    public function testGetCacher() : void
    {
        $request = $this->getFakeRequest(true, 'GET');
        $request->attributes->set('_cacher', 'cacher');
        $result = $this->obTestObject->getCacher($request);

        $this->assertInstanceOf(CacherService::class, $result, 'Не тот кэшер.');
    }

    /**
     * getCacher(). Неверный интерфейс кэшера.
     *
     * @return void
     */
    public function testGetCacherInvalidInterface() : void
    {
        $request = $this->getFakeRequest(true, 'GET');
        $request->attributes->set('_cacher', 'dummy_service');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cacher must implementing CacheInterface.');

        $this->obTestObject->getCacher($request);
    }

    /**
     * getCacher(). Нет параметра _cacher.
     *
     * @return void
     */
    public function testGetCacherInvalidParams() : void
    {
        $request = $this->getFakeRequest(true, 'GET');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cacher with ID  not exists. You dont forget set _cacher options in route file?');

        $this->obTestObject->getCacher($request);
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
