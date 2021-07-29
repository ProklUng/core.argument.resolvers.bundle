<?php

namespace Prokl\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers;

use Exception;
use Prokl\CustomArgumentResolverBundle\Event\Listeners\AjaxCall;
use Prokl\CustomArgumentResolverBundle\Service\ArgumentResolvers\DelegatingContainerArgumentResolver;
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
 * @coversDefaultClass DelegatingContainerArgumentResolver
 *
 * @since 29.07.2021
 */
class DelegatingContainerArgumentResolverTest extends BaseTestCase
{
    /**
     * @var AjaxCall $obTestObject Тестируемый объект.
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

        $this->obTestObject = new DelegatingContainerArgumentResolver($iterable);
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

        $result = $this->obTestObject->supports(new Request(), $argument);

        $this->assertTrue($result, 'Сервис не зацепился.');
    }

    /**
     * supports(). Нормальный ход вещей.
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

        $result = $this->obTestObject->supports(new Request(), $argument);

        $this->assertFalse($result, 'Зацепился левый сервис.');
    }

    /**
     * supports(). Нормальный ход вещей.
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

        $this->obTestObject = new DelegatingContainerArgumentResolver($iterable);

        $argument = new ArgumentMetadata(
            'foo',
            FooService::class,
            false,
            false,
            false
        );

        $result = $this->obTestObject->supports(new Request(), $argument);

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

        $result = $this->obTestObject->resolve(new Request(), $argument);

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
            FakeService::class,
            false,
            false,
            false
        );

        $result = $this->obTestObject->resolve(new Request(), $argument);
        $array = iterator_to_array($result);

        $this->assertNull($array[0], 'Проскочило что-то странное.');
     }

    /**
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
}
