<?php
namespace Idealogica\RouteOne\MiddlewareDispatcher;

use mindplay\middleman\Dispatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class MiddlemanMiddlewareDispatcher
 * @package Idealogica\RouteOne
 */
class MiddlemanMiddlewareDispatcher extends AbstractMiddlewareDispatcher
{
    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request)
    {
        return (new Dispatcher($this->getMiddlewares()))->dispatch($request);
    }
}
