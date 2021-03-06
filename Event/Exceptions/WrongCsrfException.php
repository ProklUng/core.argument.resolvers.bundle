<?php

namespace Prokl\CustomArgumentResolverBundle\Event\Exceptions;

use Prokl\BaseException\BaseException;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;

/**
 * Class WrongCsrfException
 * Исключения классов пространства имен Events.
 * @package Prokl\CustomArgumentResolverBundle\Event\Exceptions
 *
 * @sinсe 05.09.2020
 * @since 10.09.2020 Implement RequestExceptionInterface.
 */
class WrongCsrfException extends BaseException implements RequestExceptionInterface
{

}
