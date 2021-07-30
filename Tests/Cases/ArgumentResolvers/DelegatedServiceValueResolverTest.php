<?php

namespace Prokl\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers;

use Exception;
use Prokl\CustomArgumentResolverBundle\Service\ArgumentResolvers\DelegatedServiceValueResolver;
use Prokl\CustomArgumentResolverBundle\Tests\Samples\FooController;
use Prokl\CustomArgumentResolverBundle\Tests\Samples\FooService;
use Prokl\CustomArgumentResolverBundle\Tests\Tools\BaseTestCase;
use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Class DelegatingContainerArgumentResolverTest
 * @package Prokl\CustomArgumentResolverBundle\Tests\Listeners
 * @coversDefaultClass DelegatedServiceValueResolver
 *
 * @since 30.07.2021
 */
class DelegatedServiceValueResolverTest extends BaseTestCase
{
    /**
     * @var DelegatedServiceValueResolver $obTestObject Тестируемый объект.
     */
    protected $obTestObject;

    /**
     * @inheritdoc
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $iterable = new RewindableGenerator(
            function () {
                yield $this->getDelegateContainer();
            },
            1
        );

        $this->obTestObject = new DelegatedServiceValueResolver($this->getOrdinaryContainer());
        $this->obTestObject->setDelegatedContainers($iterable);
    }

    /**
     * supports(). Нормальный ход вещей.
     *
     * @return void
     * @throws Exception
     */
    public function testSupports() : void
    {
        $argument = new ArgumentMetadata(
            'foo',
            FooService::class,
            false,
            false,
            false
        );

        $result = $this->obTestObject->supports($this->getRequest(), $argument);

        $this->assertTrue($result, 'Сервис не зацепился.');
    }

    /**
     * supports(). Невалидный класс.
     *
     * @return void
     * @throws Exception
     */
    public function testSupportsInvalidClass() : void
    {
        $argument = new ArgumentMetadata(
            'foo',
            FakeService::class,
            false,
            false,
            false
        );

        $result = $this->obTestObject->supports($this->getRequest(), $argument);

        $this->assertTrue($result, 'Зацепился левый сервис.');
    }

    /**
     * supports(). Пустой делегированный контейнер.
     *
     * @return void
     * @throws Exception
     */
    public function testSupportsEmptyContainer() : void
    {
        $iterable = new RewindableGenerator(
            function () {
                $container = new ContainerBuilder();
                $container->compile(false);
                yield $container;
            },
            1
        );

        $this->obTestObject = new DelegatedServiceValueResolver($this->getOrdinaryContainer());
        $this->obTestObject->setDelegatedContainers($iterable);

        $argument = new ArgumentMetadata(
            'foo',
            FooService::class,
            false,
            false,
            false
        );

        $result = $this->obTestObject->supports($this->getRequest(), $argument);

        $this->assertFalse($result, 'Зацепился сервис при пустом контейнере.');
    }

    /**
     * resolve()
     *
     * @return void
     */
    public function testResolve() : void
    {
        $argument = new ArgumentMetadata(
            'foo',
            FooService::class,
            false,
            false,
            false
        );

        $result = $this->obTestObject->resolve($this->getRequest(), $argument);

        $array = iterator_to_array($result);

        $this->assertInstanceOf(
            FooService::class,
            $array[0],
            'Не тот класс'
        );
    }

    /**
     * resolve()
     *
     * @return void
     */
    public function testResolveOtherArgument() : void
    {
        $argument = new ArgumentMetadata(
            'foo',
            FooService::class,
            false,
            false,
            false
        );

        $result = $this->obTestObject->resolve($this->getRequest(FakeService::class), $argument);
        $array = iterator_to_array($result);

        $this->assertNull($array[0], 'Проскочило что-то странное.');
    }

    /**
     * Делегированный контейнер.
     *
     * @return ContainerBuilder
     */
    private function getDelegateContainer() : ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setDefinition(
            FooService::class,
            new Definition(FooService::class)
        )->setPublic(true);

        $container->compile(false);

        return $container;
    }

    /**
     * @return ContainerBuilder
     */
    private function getOrdinaryContainer(): ContainerBuilder
    {
        $ordinaryContainer = new ContainerBuilder();
        $ordinaryContainer->setDefinition(
            FooController::class,
            new Definition(FooController::class)
        )->setPublic(true);

        $ordinaryContainer->compile(false);

        return $ordinaryContainer;
    }

    /**
     * Request.
     *
     * @param string $foo
     *
     * @return Request
     */
    private function getRequest(string $foo = FooService::class): Request
    {
        $request = new Request();
        $request->attributes->set(
            '_controller',
            'Prokl\CustomArgumentResolverBundle\Tests\Samples\FooController::action'
        );

        $request->attributes->set(
            'foo',
            '@' . $foo
        );

        return $request;
    }
}
