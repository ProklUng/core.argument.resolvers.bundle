<?php

namespace Prokl\CustomArgumentResolverBundle\Tests\Samples;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SampleService
 * @package Prokl\CustomArgumentResolverBundle\Tests\Samples
 *
 * @since 06.12.2020
 */
class SampleService
{
    public function action(Request $request)
    {
        return new Response('OK');
    }
}