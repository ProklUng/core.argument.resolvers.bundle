<?php

namespace Prokl\CustomArgumentResolverBundle\Tests\Cases\Services;

use Exception;
use Prokl\CustomArgumentResolverBundle\Event\InjectorController\CustomArgumentResolverProcessor;
use Prokl\CustomArgumentResolverBundle\Tests\Samples\SampleController;
use Prokl\CustomArgumentResolverBundle\Tests\Samples\SampleControllerArguments;
use Prokl\CustomArgumentResolverBundle\Tests\Samples\SampleControllerDependency;
use Prokl\CustomArgumentResolverBundle\Tests\Tools\BaseTestCase;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class CommonProcessor
 * @package Prokl\CustomArgumentResolverBundle\Tests\Cases\Services
 * @coversDefaultClass CustomArgumentResolverProcessor
 *
 * @since 05.12.2020 Актуализация.
 */
class CustomArgumentResolverProcessorTest extends BaseTestCase
{
    /**
     * @var CustomArgumentResolverProcessor $testObject Тестируемый объект.
     */
    protected $testObject;

    /**
     * @var string $controllerClass Класс контроллера для теста.
     */
    private $controllerClass = SampleController::class;

    /**
     * @inheritdoc
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->testObject = new CustomArgumentResolverProcessor(
            $this->container->get('custom_arguments_resolvers.container.aware.resolver'),
            $this->container->get('custom_arguments_resolvers.ignored.autowiring.controller.arguments'),
        );

        $this->testObject->setContainer($this->container);
    }

    /**
     * Разрешение переменных из контейнера.
     */
    public function testResolveVariableFromContainer(): void
    {
        $request = new Request(
            [],
            [],
            [
                '_controller' => '\Prokl\CustomArgumentResolverBundle\Tests\Samples\SampleControllerArguments::action',
                'obj' => SampleControllerArguments::class,
                'value' => '%my.instagram.token%',
            ]
        );

        $event = $this->getMockControllerEvent($request);

        $result = $this->testObject->inject(
            $event
        );

        $attributes = $result->getRequest()->attributes->all();

        $this->assertNotEmpty(
            $attributes
        );
    }

    /**
     * Разрешение Session.
     *
     * @return void
     * @throws Exception
     */
    public function testSessionResolve(): void
    {
        $request = new Request(
            [],
            [],
            [
                '_controller' => '\Prokl\CustomArgumentResolverBundle\Tests\Samples\SampleController::action2',
                'obj' => SampleController::class,
            ]
        );

        $event = $this->getMockControllerEvent($request);

        $this->testObject->inject($event);

        $this->assertInstanceOf(
            SessionInterface::class,
            $event->getRequest()->attributes->get('session')
        );
    }

    /**
     * Разрешение Defaults value.
     */
    public function testDefaultsResolve(): void
    {
        $request = new Request(
            [],
            [],
            [
                '_controller' => '\Prokl\CustomArgumentResolverBundle\Tests\Samples\SampleController::action3',
                'obj' => SampleController::class,
            ]
        );

        $event = $this->getMockControllerEvent($request);

        $this->testObject->inject(
            $event
        );

        $this->assertSame(
            'OK',
            $event->getRequest()->attributes->get('value')
        );
    }

    /**
     * Разрешение Defaults value. Не портит ли значение по умолчанию передаваемое значение.
     */
    public function testWithoutDefaults(): void
    {
        $request = new Request(
            [],
            [],
            [
                '_controller' => '\Prokl\CustomArgumentResolverBundle\Tests\Samples\SampleController::action3',
                'obj' => SampleController::class,
            ]
        );

        $request->attributes->add(['value' => 'OK3']);

        $event = $this->getMockControllerEvent($request);

        $this->testObject->inject(
            $event
        );

        $this->assertSame(
            'OK3',
            $event->getRequest()->attributes->get('value')
        );
    }

    /**
     * Разрешение Defaults value. Constants.
     *
     * @return void
     */
    public function testDefaultsResolveConstants(): void
    {
        $request = new Request(
            [],
            [],
            [
                '_controller' => '\Prokl\CustomArgumentResolverBundle\Tests\Samples\SampleController::action4',
                'obj' => SampleController::class,
            ]
        );

        $event = $this->getMockControllerEvent($request);

        $this->testObject->inject(
            $event
        );

        $this->assertSame(
            'OK3',
            $event->getRequest()->attributes->get('value')
        );
    }

    /**
     * Разрешение Defaults value. Array
     */
    public function testDefaultsResolveArray(): void
    {
        $request = new Request(
            [],
            [],
            [
                '_controller' => '\Prokl\CustomArgumentResolverBundle\Tests\Samples\SampleController::action4',
                'obj' => SampleController::class,
            ]
        );

        $event = $this->getMockControllerEvent($request);

        $this->testObject->inject(
            $event
        );

        $this->assertSame(
            [1, 2, 3],
            $event->getRequest()->attributes->get('array')
        );
    }

    /**
     * Разрешение Defaults value. Array recursively.
     *
     * @return void
     * @throws Exception
     */
    public function testDefaultsResolveArrayRecursive(): void
    {
        $request = new Request(
            [],
            [],
            [
                '_controller' => '\Prokl\CustomArgumentResolverBundle\Tests\Samples\SampleController::action5',
                'obj' => SampleController::class,
            ]
        );

        $event = $this->getMockControllerEvent($request);

        $this->testObject->inject(
            $event
        );

        $this->assertSame(
            [
                1,
                2,
                [$this->container->getParameter('kernel.cache_dir')],
                [$this->container->get('session.instance')],
            ],
            $event->getRequest()->attributes->get('array')
        );
    }

    /**
     * Разрешение Invalid service alias.
     *
     * @return void
     * @throws Exception
     */
    public function testDefaultsResolveInvalidServiceAlias(): void
    {
        $request = new Request(
            [],
            [],
            [
                '_controller' => '\Prokl\CustomArgumentResolverBundle\Tests\Samples\SampleController::action6',
                'obj' => SampleController::class,
            ]
        );

        $event = $this->getMockControllerEvent($request);

        $this->willSeeException(ServiceNotFoundException::class);
        $this->testObject->inject(
            $event
        );
    }

    /**
     * Разрешение Invalid variable.
     *
     * @return void
     * @throws Exception
     */
    public function testDefaultsResolveInvalidVariable(): void
    {
        $request = new Request(
            [],
            [],
            [
                '_controller' => '\Prokl\CustomArgumentResolverBundle\Tests\Samples\SampleController::action7',
                'obj' => SampleController::class,
            ]
        );

        $event = $this->getMockControllerEvent($request);

        $this->testObject->inject(
            $event
        );

        $this->assertSame(
            '%invalid.variable%',
            $event->getRequest()->attributes->get('value')
        );
    }

    /**
     * Инжекция не объектов, а переменных.
     *
     * @return void
     * @throws Exception
     */
    public function testInjectNonObjects(): void
    {
        $request = new Request(
            [],
            [],
            [
                '_controller' => '\Prokl\CustomArgumentResolverBundle\Tests\Samples\SampleController::action8',
                'obj' => SampleController::class,
            ]
        );

        $request->attributes->add(['value' => 'OK3', 'id' => 1]);

        $event = $this->getMockControllerEvent($request);

        $this->testObject->inject(
            $event
        );

        $this->assertSame(
            'OK3',
            $event->getRequest()->attributes->get('value')
        );

        $this->assertSame(
            1,
            $event->getRequest()->attributes->get('id')
        );

    }

    /**
     * Мок ControllerEvent.
     *
     * @param Request|null $request
     * @param array        $args
     *
     * @return mixed|ControllerEvent
     */
    private function getMockControllerEvent(Request $request = null, array $args = [])
    {

        if ($request === null) {
            $request = $this->getFakeRequest();
        }

        $controller = $request->attributes->get('_controller');

        return new ControllerArgumentsEvent(
            $this->getMockKernel(),
            $controller,
            $args,
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );
    }

    /**
     * Создать фэйковый Request.
     *
     * @return Request
     */
    private function getFakeRequest(): Request
    {
        $fakeRequest = Request::create(
            '/api/elastic/search/',
            'GET',
            []
        );

        $fakeRequest->attributes->set('_controller', $this->controllerClass.'::action');
        $fakeRequest->attributes->set('obj', SampleControllerDependency::class);
        $fakeRequest->headers = new HeaderBag(['x-requested-with' => 'XMLHttpRequest']);

        return $fakeRequest;
    }
}
