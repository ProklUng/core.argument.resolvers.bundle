<?php

namespace Prokl\CustomArgumentResolverBundle\Tests\Samples;

/**
 * Class SampleServiceNested
 * @package Prokl\CustomArgumentResolverBundle\Tests\Samples
 */
class SampleServiceNested
{
    protected $ob;

    public function __construct(SampleDependencyNested $ob)
    {
        $this->ob = $ob;
    }

    public function nested() : int
    {
        return $this->ob->nested();
    }
}
