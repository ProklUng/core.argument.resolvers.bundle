<?php

namespace Prokl\CustomArgumentResolverBundle\Tests\Cases\Services;

use Prokl\CustomArgumentResolverBundle\Service\ResolversDependency\ResolveDependencyMaker;
use Prokl\CustomArgumentResolverBundle\Tests\Samples\SampleDependencyInterface;
use Prokl\CustomArgumentResolverBundle\Tests\Samples\SampleDependencyInterfaceRealization;
use Prokl\CustomArgumentResolverBundle\Tests\Samples\SampleDependencyInterfaceUnrealized;
use Prokl\CustomArgumentResolverBundle\Tests\Samples\SampleInjectableAutoResolved;
use Prokl\CustomArgumentResolverBundle\Tests\Samples\SampleServiceNested;
use Prokl\CustomArgumentResolverBundle\Tests\Tools\BaseTestCase;
use Prokl\CustomArgumentResolverBundle\Tests\Tools\PHPUnitUtils;

/**
 * Class ResolveDependencyMaker
 * @package Tests\Objects
 * @coversDefaultClass ResolveDependencyMaker
 */
class ResolveDependencyMakerTest extends BaseTestCase
{
    /**
     * @var ResolveDependencyMaker $testObject
     */
    protected $testObject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testObject = new ResolveDependencyMaker();
    }

    /**
    * resolve(). Класс без зависимостей и конструктора.
    */
    public function testClassWithoutDependencyAndConstructor() : void
    {
        $class = get_class(new class {
        });

        $result = $this->testObject->resolveDependencies($class);

        $this->assertInstanceOf(
            $class,
            $result,
            'Класс без зависимостей и конструктора проскочил.'
        );
    }

    /**
     * resolve(). Класс без зависимостей, но с конструктором.
     */
    public function testClassWithoutDependencyWithConstructor() : void
    {
        $class = get_class(new class {
            /**
             * @var string
             */
            private $value;

            public function __construct(string $value = 'test')
            {
                $this->value = $value;
            }
        });

        $result = $this->testObject->resolveDependencies($class);

        $this->assertInstanceOf(
            $class,
            $result,
            'Класс без зависимостей и конструктора проскочил.'
        );

        $result = PHPUnitUtils::getProtectedProperty(
            $result,
            'value'
        );

        $this->assertSame(
            'test',
            $result,
            'Параметр value не проскочил.'
        );
    }

    /**
     * resolve(). Класс с вложенными зависимостями.
     */
    public function testClassNestedDependency() : void
    {
        $class = SampleServiceNested::class;

        $result = $this->testObject->resolveDependencies($class);

        $this->assertInstanceOf(
            $class,
            $result,
            'Класс без зависимостей и конструктора проскочил.'
        );
    }

    /**
     * tryResolveInterface ().
     */
    public function testTryResolveInterface(): void
    {
        $value = [
            SampleDependencyInterfaceRealization::class,
        ];

        $result = PHPUnitUtils::callMethod(
            $this->testObject,
            'tryResolveInterface',
            [SampleDependencyInterface::class, $value]
        );

        $this->assertSame(
            $value[0],
            $result,
            'Неправильно сопоставлен класс и интерфейс.'
        );
    }

    /**
     * tryResolveInterface (). Invalid interface.
     */
    public function testTryResolveInterfaceInvalid(): void
    {
        $value = [
            SampleDependencyInterfaceRealization::class,
        ];

        $result = PHPUnitUtils::callMethod(
            $this->testObject,
            'tryResolveInterface',
            [SampleDependencyInterfaceUnrealized::class, $value]
        );

        $this->assertEmpty(
            $result,
            'Несуществующая связка проскочила.'
        );
    }

    /**
     * setDepends ().
     */
    public function testSetDepends() : void
    {
        $value = [
            'test' => 'test'
        ];

        $this->testObject->setDepends($value);

        $this->assertSameProtectedProp(
            'arDepends',
            $value,
            'Сеттер не сработал.'
        );
    }
}
