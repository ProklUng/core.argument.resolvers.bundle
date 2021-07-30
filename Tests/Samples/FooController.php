<?php
/** @noinspection PhpUnusedParameterInspection */

namespace Prokl\CustomArgumentResolverBundle\Tests\Samples;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SampleController
 * @package Tests\Events\Samples
 * @codeCoverageIgnore
 */
class FooController extends AbstractController
{
    public function action(
        Request $request,
        SampleControllerDependency $obj
    ) {
        return new Response('OK');
    }
}
