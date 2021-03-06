<?php

namespace Prokl\CustomArgumentResolverBundle\Tests\Samples;

/**
 * Trait SampleBootTrait
 * @package Prokl\CustomArgumentResolverBundle\Tests\Samples
 *
 * @since 06.12.2020
 */
trait SampleBootTrait
{
    public static $booted = false;
    public static $init = false;

    public function initializeSampleBootTrait()
    {
        self::$init = true;
        return true;
    }

    public static function bootSampleBootTrait()
    {
        self::$booted = true;
        return true;
    }
}