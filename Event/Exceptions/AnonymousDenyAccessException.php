<?php

namespace Prokl\CustomArgumentResolverBundle\Event\Exceptions;

use Prokl\BaseException\BaseException;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;

/**
 * Class AnonymousDenyAccessException
 * Исключения классов пространства имен Events.
 * @package Prokl\CustomArgumentResolverBundle\Event\Exceptions
 *
 * @sine 18.02.2021
 */
class AnonymousDenyAccessException extends BaseException implements RequestExceptionInterface
{

}
