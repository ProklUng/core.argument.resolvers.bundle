<?php

namespace Prokl\CustomArgumentResolverBundle\Tests\Tools;

use Faker\Factory;
use Faker\Generator;
use Mockery;
use PHPUnit\Framework\TestCase;
use Prokl\TestingTools\Tools\Container\BuildContainer;
use Prokl\TestingTools\Traits\ExceptionAsserts;
use Prokl\TestingTools\Traits\PHPUnitTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BaseTestCase
 * @package Prokl\CustomArgumentResolverBundle\Tests\Tools
 *
 * @since 05.12.2020
 */
class BaseTestCase extends \Prokl\TestingTools\Base\BaseTestCase
{
    use ExceptionAsserts;
    use PHPUnitTrait;

    /**
     * @var mixed $testObject Тестируемый объект.
     */
    protected $testObject;

    /**
     * @var Generator | null $faker
     */
    protected $faker;

    /**
     * @var ContainerInterface $testContainer Тестовый контейнер.
     */
    protected static $testContainer;

    protected function setUp(): void
    {
        // Инициализация тестового контейнера.
        $this->container = static::$testContainer = BuildContainer::getTestContainer(
            [
                'dev/test_container.yaml',
                'services.yaml',
                'listeners.yaml'
            ],
            '/Resources/config'
        );

        parent::setUp();
    }
}
