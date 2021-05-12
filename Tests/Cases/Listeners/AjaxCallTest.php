<?php

namespace Prokl\CustomArgumentResolverBundle\Tests\Cases\Listeners;

use Exception;
use Mockery;
use Prokl\CustomArgumentResolverBundle\Event\Exceptions\InvalidAjaxCallException;
use Prokl\CustomArgumentResolverBundle\Event\Listeners\AjaxCall;
use Prokl\CustomArgumentResolverBundle\Event\Traits\ValidatorTraits\SecurityAjaxCallTrait;
use Prokl\CustomArgumentResolverBundle\Tests\Tools\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class AjaxCallTest
 * @package Prokl\CustomArgumentResolverBundle\Tests\Listeners
 * @coversDefaultClass AjaxCall
 *
 * @since 10.09.2020
 * @since 28.10.2020 Рефакторинг.
 * @since 05.12.2020 Актуализация.
 * @since 06.12.2020 Рефакторинг.
 */
class AjaxCallTest extends BaseTestCase
{
    /**
     * @var AjaxCall $obTestObject Тестируемый объект.
     */
    protected $obTestObject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obTestObject = new AjaxCall();
    }

    /**
     * handle(). Нормальный ход вещей.
     *
     * @return void
     * @throws Exception
     */
    public function testHandle() : void
    {
        $this->obTestObject->handle($this->getMockControllerEvent());

        $this->assertTrue(
            true
        );
    }

    /**
     * handle(). Не AJAX.
     *
     * @return void
     * @throws Exception
     */
    public function testHandleNoAjax() : void
    {
        $event = $this->getMockControllerEvent(false);
        $event->getRequest()->headers->replace([]);

        $this->expectException(InvalidAjaxCallException::class);
        $this->obTestObject->handle($event);
    }

    /**
     * Мок ControllerEvent.
     *
     * @param bool $ajax
     *
     * @return mixed
     * @throws Exception
     */
    private function getMockControllerEvent(bool $ajax = true)
    {
        $controllerResolver = new ControllerResolver();

        $request = $this->getFakeRequest($ajax);
        $controller = $controllerResolver->getController($request);

        return new ControllerEvent(
            $this->getMockKernel(),
            $controller,
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );
    }

    /**
     * Создать фэйковый Request.
     *
     * @param boolean $ajax
     *
     * @return Request
     */
    private function getFakeRequest(bool $ajax = true): Request
    {
        $fakeRequest = Request::create(
            '/api/fake/',
            'GET',
            []
        );

        $class = new class extends AbstractController{
            use SecurityAjaxCallTrait;

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
        if ($ajax) {
            $fakeRequest->headers = new HeaderBag(['x-requested-with' => 'XMLHttpRequest']);
        }

        return $fakeRequest;
    }
}
