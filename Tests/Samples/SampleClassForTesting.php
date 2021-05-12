<?php

namespace Prokl\CustomArgumentResolverBundle\Tests\Samples;

use Fedy\Logger\MyLogger;

/**
 * Class SampleClassForTesting
 * @package Prokl\CustomArgumentResolverBundle\Tests\Samples
 * @codeCoverageIgnore
 */
class SampleClassForTesting
{
    /**
     * @var MyLogger
     */
    private $logger;

    public function __construct(MyLogger $logger)
    {
        $this->logger = $logger;
    }

    public function check(): void
    {

    }
}
