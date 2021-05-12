<?php

namespace Prokl\CustomArgumentResolverBundle\Tests\Samples;

/**
 * Class SampleDependency
 * @package Prokl\CustomArgumentResolverBundle\Tests\Samples
 * @codeCoverageIgnore
 */
class SampleDependency
{
    public function get(): int
    {
        return 222;
    }
}
