<?php

namespace Prokl\CustomArgumentResolverBundle\Event\Exceptions;

use Prokl\BaseException\BaseException;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;

/**
 * Class InvalidConfigArgumentException
 * Исключения классов пространства имен Events.
 * @package Prokl\CustomArgumentResolverBundle\Event\Exceptions
 *
 * @sine 04.12.2020
 */
class InvalidConfigArgumentException extends BaseException implements RequestExceptionInterface
{

}
