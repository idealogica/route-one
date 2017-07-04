<?php
namespace Idealogica\RouteOne\Exception;

use Throwable;

/**
 * Class RouteOneException
 * @package Idealogica\RouteOne\Exception
 */
class RouteOneException extends \Exception
{
    /**
     * RouteOneException constructor.
     *
     * @param string $message
     * @param array $printfArgs
     * @param Throwable|null $previous
     */
    public function __construct($message, array $printfArgs = [], Throwable $previous = null)
    {
        parent::__construct(sprintf($message, ...$printfArgs), 0, $previous);
    }
}
