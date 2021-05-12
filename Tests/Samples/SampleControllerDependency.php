<?php

namespace Prokl\CustomArgumentResolverBundle\Tests\Samples;
/**
 * Class SampleControllerDependency
 * @package Prokl\CustomArgumentResolverBundle\Tests
 * @codeCoverageIgnore
 */
class SampleControllerDependency
{
    public function get(): int
    {
        return 2;
    }
}
