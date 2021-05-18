<?php

namespace Prokl\CustomArgumentResolverBundle\Tests\Cases;

use Exception;
use Mockery;
use Prokl\CustomArgumentResolverBundle\Event\Listeners\JsonParamsConvertor;
use Prokl\TestingTools\Base\BaseTestCase;
use Prokl\TestingTools\Traits\DataProviders\Elements;
use Prokl\TestingTools\Traits\DataProvidersTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class JsonParamsConvertorTest
 * @package Tests\OnControllerRequest
 * @coversDefaultClass JsonParamsConvertor
 *
 * @since 27.10.2020
 */
class JsonParamsConvertorTest extends BaseTestCase
{
    use DataProvidersTrait;

    /**
     * @var JsonParamsConvertor $obTestObject Тестируемый объект.
     */
    protected $obTestObject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obTestObject = new JsonParamsConvertor();
    }

    /**
     * handle(). Valid data.
     *
     * @return void
     * @throws Exception
     */
    public function testHandle() : void
    {
        $testArray = [
          'test' => 22
        ];

        $request = $this->getRequest(json_encode($testArray));
        $event = $this->getEventSubscriber($request);

        $this->obTestObject->handle($event);
        $data = $event->getRequest()->request->all();

        $this->assertIsArray(
            $data,
            'В ответе не массив.'
        );

        $this->assertEquals(
            $testArray,
            $data,
            'Обработка не прошла.'
        );
    }

    /**
     * handle(). Invalid json.
     *
     * @return void
     * @throws Exception
     */
    public function testHandleInvalidJson() : void
    {
        $string = $this->faker->sentence;

        $request = $this->getRequest($string);
        $event = $this->getEventSubscriber($request);

        $this->obTestObject->handle($event);

        $data = $event->getResponse()->getContent();

        $this->assertSame(
            'Unable to parse request.',
            $data,
            'Обработка не прошла.'
        );
    }

    /**
     * handle(). Не json в content-type.
     *
     * @param string $contentType
     *
     * @return void
     * @throws Exception
     * @dataProvider providerValidContentTypes
     */
    public function testHandleOtherContentType(string $contentType) : void
    {
        $string = $this->faker->sentence;

        $request = $this->getRequest($string, $contentType);
        $event = $this->getEventSubscriber($request);

        $this->obTestObject->handle($event);

        $this->assertSame(
            $string,
            $event->getRequest()->getContent(),
            'Обработка не прошла.'
        );
    }

    /**
     * DataProvider валидных типов content-type.
     *
     * @return mixed
     */
    public function providerValidContentTypes()
    {
        return $this->provideDataFrom([
            new Elements([
                'text/html', 'application/xhtml+xml',
                'text/plain',
                'application/javascript', 'application/x-javascript', 'text/javascript',
                'text/css',
                'application/ld+json',
                'text/xml', 'application/xml', 'application/x-xml',
                'application/rdf+xml',
                'application/atom+xml',
                'application/rss+xml',
                'application/x-www-form-urlencoded',
            ])
        ]);
    }
    /**
     * @param Request $request
     *
     * @return RequestEvent
     * @throws Exception
     */
    private function getEventSubscriber(Request $request): RequestEvent
    {
        return new RequestEvent(
            Mockery::mock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );
    }

    /**
     * @param string $content
     * @param string $contentType
     *
     * @return Request
     */
    private function getRequest(string $content, string $contentType = 'application/json'): Request
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            $content
        );

        $request->headers->set('CONTENT_TYPE', $contentType);

        return $request;
    }
}
