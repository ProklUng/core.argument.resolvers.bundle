<?php

namespace Prokl\CustomArgumentResolverBundle\Tests\Cases\Listeners;

use JsonException;
use Prokl\CustomArgumentResolverBundle\Event\Listeners\FormUrlencodedTreatment;
use Prokl\CustomArgumentResolverBundle\Tests\Tools\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class FormUrlencodedTreatmentTest
 * @package Prokl\CustomArgumentResolverBundle\Tests\Listeners
 * @coversDefaultClass FormUrlencodedTreatment
 *
 * @since 06.12.2020
 */
class FormUrlencodedTreatmentTest extends BaseTestCase
{
    /**
     * @var FormUrlencodedTreatment $obTestObject Тестируемый объект.
     */
    protected $obTestObject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obTestObject = new FormUrlencodedTreatment();
    }

    /**
     * handle(). Нормальный ход вещей.
     *
     * @param string     $contentType
     * @param array|null $content
     *
     * @return void
     * @throws JsonException
     * @dataProvider dataProviderValidContentType
     */
    public function testHandle(string $contentType, array $content = null) : void
    {
        $event = $this->getMockRequestEvent($contentType, $content);

        $this->obTestObject->handle($event);

        $result = $event->getRequest()->request->all();

        $this->assertIsArray(
            $result,
            'Не массив в Request. Не сработало'
        );

        $this->assertSame(
            $content,
            $result,
            'Не сработало. Массивы не равны.'
        );
    }

    /**
     * handle(). Не поддерживаемые MIME типы.
     *
     * @param string     $contentType
     * @param array|null $content
     *
     * @return void
     * @throws JsonException
     * @dataProvider dataProviderInvalidContentType
     */
    public function testHandleUnsupportedMimeTypes(string $contentType, array $content = null) : void
    {
        $event = $this->getMockRequestEvent($contentType, $content);

        $this->obTestObject->handle($event);

        $result = $event->getRequest()->request->all();

        $this->assertSame(
            json_encode($content),
            $event->getRequest()->getContent(),
            'Попытка обработки при неверном MIME типе.'
        );

        $this->assertEmpty(
            $result,
            'Попытка обработки при неверном MIME типе.'
        );
    }

    /**
     * Датапровайдер обрабатываемых ContentType.
     *
     * @return string[][]
     */
    public function dataProviderValidContentType() : array
    {
        return [
          'x-www-form-urlencoded-string' => [ 'application/x-www-form-urlencoded', ['fake_slug' => 'fake_slug'] ],
          'x-www-form-urlencoded-integer' =>[ 'application/x-www-form-urlencoded', ['fake_slug' => 111] ],
          'x-www-form-urlencoded-nulled' =>[ 'application/x-www-form-urlencoded', ['fake_slug' => null] ],
          'application/json-integer' => [ 'application/json', ['fake_slug' => 111] ],
          'application/json-string' => [ 'application/json', ['fake_slug' => 'fake_slug'] ],
          'application/json-nulled' => [ 'application/json', ['fake_slug' => null] ],
          'application/json-void' => [ 'application/json', [] ],
        ];
    }

    /**
     * Датапровайдер не обрабатываемых ContentType.
     *
     * @return string[][]
     */
    public function dataProviderInvalidContentType() : array
    {
        return [
            'application/html' => [ 'application/html', ['fake_slug' => 'fake_slug'] ],
            'application/atom+xml' => [ 'application/atom+xml', ['fake_slug' => 'fake_slug'] ],
            'application/javascript' => [ 'application/javascript', ['fake_slug' => 'fake_slug'] ],
            'application/gzip' => [ 'application/gzip', ['fake_slug' => 'fake_slug'] ],
            'application/json-null' => [ 'application/json', null ],
        ];
    }

    /**
     * Мок RequestEvent.
     *
     * @param string|null $contentType Тип MIME.
     * @param array|null  $content     Контент.
     *
     * @return mixed
     */
    private function getMockRequestEvent(string $contentType = null, array $content = null)
    {
        $request = $this->getFakeRequest($contentType, $content);

        return new RequestEvent(
            $this->getMockKernel(),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );
    }

    /**
     * Создать фэйковый Request.
     *
     * @param string|null $contentType Тип MIME.
     * @param array|null  $postData    Данные для POST запроса.
     *
     * @return Request
     */
    private function getFakeRequest(string $contentType = null, array $postData = null): Request
    {
        $fakeRequest = Request::create(
            '/api/fake/',
            'POST',
            [],
            [],
            [],
            [],
            json_encode($postData)
        );

        $class = new class extends AbstractController{
            public function action(Request $request)
            {
                return new Response('OK');
            }
        };

        $controllerString = get_class($class) . '::action';

        $fakeRequest->attributes->set('_controller',
            $controllerString
        );

        $fakeRequest->attributes->set('obj', get_class($class));

        if ($contentType) {
            $fakeRequest->headers = new HeaderBag(['content-type' => $contentType]);
        }

        return $fakeRequest;
    }
}
