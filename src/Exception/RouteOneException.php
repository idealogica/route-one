<?php
namespace Idealogica\RouteOne\Exception;

use Throwable;

/**
 * Class RouteOneException
 * @package Idealogica\RouteOne\Exception
 */
class RouteOneException extends \Exception
{
    public function __construct($message, array $printfArgs = [], Throwable $previous = null)
    {
        parent::__construct(sprintf($message, ...$printfArgs), 0, $previous);
    }
}
