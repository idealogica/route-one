<?php
namespace Idealogica\RouteOne\MiddlewareDispatcher;

use Idealogica\RouteOne\RouteMiddleware\RouteMiddlewareInterface;
use Idealogica\RouteOne\UriGenerator\UriGeneratorInterface;
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
     * @var null|callable
     */
    protected $middlewareResolver = null;

    /**
     * MiddlewareDispatcher constructor.
     *
     * @param callable|null $middlewareResolver
     * @param RouteMiddlewareInterface|null $defaultRouteMiddleware
     * @param UriGeneratorInterface|null $uriGenerator
     */
    public function __construct(
        RouteMiddlewareInterface $defaultRouteMiddleware,
        UriGeneratorInterface $uriGenerator,
        callable $middlewareResolver = null
    ) {
        parent::__construct($defaultRouteMiddleware, $uriGenerator);
        $this->middlewareResolver = $middlewareResolver;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request)
    {
        return (new Dispatcher($this->getMiddlewares(), $this->middlewareResolver))->dispatch($request);
    }
}
