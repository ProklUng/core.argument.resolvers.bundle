<?php

namespace Prokl\CustomArgumentResolverBundle\Tests\Cases\Listeners;

use Exception;
use Prokl\CustomArgumentResolverBundle\Event\InjectorController\InjectorControllerInterface;
use Prokl\CustomArgumentResolverBundle\Event\Listeners\ResolverParamsController;
use Prokl\CustomArgumentResolverBundle\Tests\Samples\SampleService;
use Prokl\CustomArgumentResolverBundle\Tests\Tools\BaseTestCase;
use Mockery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class ResolverParamsControllerTest
 * @package Prokl\CustomArgumentResolverBundle\Tests\Listeners
 * @coversDefaultClass ResolverParamsController
 *
 * @since 06.12.2020
 */
class ResolverParamsControllerTest extends BaseTestCase
{
    /**
     * @var ResolverParamsController $obTestObject Тестируемый объект.
     */
    protected $obTestObject;

    /**
     * @var object $class Анонимный класс, подмена контроллера.
     */
    private $class;

    /**
     * handle(). Нормальный ход вещей. Класс в числе разрешенных к обработке.
     *
     * @return void
     * @throws Exception
     */
    public function testHandleProcessableClass() : void
    {
        $event = $this->getMockControllerEvent(true);

        $this->obTestObject = new ResolverParamsController(
            static::$testContainer->get('custom_arguments_resolvers.controller_argument.processor'),
            ['params' =>
                ['classes_controllers' => [
                    AbstractController::class
                ],
                    'process_only_non_service_controller' => false
                ]
            ]
        );

        $this->obTestObject->handle($event);
        $attributes = $event->getRequest()->attributes->all();

        $this->assertInstanceOf(
            SampleService::class,
            $attributes['service']
        );
    }

    /**
     * handle(). Класс не подлежащий обработке.
     *
     * @return void
     * @throws Exception
     */
    public function testHandleUnprocessableClass() : void
    {
        $event = $this->getMockControllerEvent(true);

        $this->class = new class{
            public function action(Request $request, SampleService $service)
            {
                return new Response('OK');
            }
        };

        $this->obTestObject = new ResolverParamsController(
            static::$testContainer->get('custom_arguments_resolvers.controller_argument.processor'),
            ['params' =>
                ['classes_controllers' => [
                    get_class($this->class)
                ],
                    'process_only_non_service_controller' => false
                ]
            ]
        );

        $this->obTestObject->handle($event);
        $attributes = $event->getRequest()->attributes->all();

        $this->assertArrayNotHasKey(
            'service',
            $attributes,
            'Класс, не подлежащий обработке проскочил сквозь процесс.'
        );
    }

    /**
     * handle(). SUB REQUEST.
     *
     * @return void
     * @throws Exception
     */
    public function testHandleSubRequest() : void
    {
        $this->obTestObject = new ResolverParamsController(
            $this->getMockInjectorControllerInterface(0)
        );

        $event = $this->getMockControllerEvent(false);

        $this->obTestObject->handle($event);
        $attributes = $event->getRequest()->attributes->all();

        $this->assertCount(
            2,
            $attributes,
            'Проверка на MASTER REQUEST не отработала.'
        );
    }

    /**
     * Мок ControllerEvent.
     *
     * @param boolean $masterRequest MASTER REQUEST.
     *
     * @return mixed
     */
    private function getMockControllerEvent(bool $masterRequest = true)
    {
        $controllerResolver = new ControllerResolver();

        $request = $this->getFakeRequest();

        $controller = $controllerResolver->getController($request);

        return new ControllerEvent(
            $this->getMockKernel(),
            $controller,
            $request,
            $masterRequest ? HttpKernelInterface::MASTER_REQUEST : HttpKernelInterface::SUB_REQUEST
        );
    }

    /**
     * Мок InjectorControllerInterface.
     *
     * @param integer $callTimes Сколько раз.
     *
     * @return mixed
     */
    private function getMockInjectorControllerInterface(int $callTimes = 0)
    {
        $mock = Mockery::mock(InjectorControllerInterface::class);
        $mock = $mock->shouldReceive('inject');

        if ($callTimes === 0) {
            $mock->never();
        } else {
            $mock->atLeast()->times($callTimes);
        }

        return $mock->getMock();
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

        $this->class = new class extends AbstractController{
            public function action(Request $request, SampleService $service)
            {
                return new Response('OK');
            }
        };

        $controllerString = get_class($this->class) . '::action';

        $fakeRequest->attributes->set('_controller',
            $controllerString
        );

        $fakeRequest->attributes->set('obj', get_class($this->class));

        return $fakeRequest;
    }
}
