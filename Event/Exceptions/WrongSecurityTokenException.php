<?php

namespace Prokl\CustomArgumentResolverBundle\Event\Exceptions;

use Prokl\BaseException\BaseException;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;

/**
 * Class WrongSecurityTokenException
 * Исключения классов пространства имен Events.
 * @package Prokl\CustomArgumentResolverBundle\Event\Exceptions
 *
 * @sine 09.09.2020
 */
class WrongSecurityTokenException extends BaseException implements RequestExceptionInterface
{

}
