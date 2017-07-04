<?php
namespace Idealogica\RouteOne\MiddlewareDispatcher;

use Idealogica\RouteOne\RouteMiddleware\AuraRouteMiddleware;
use Idealogica\RouteOne\RouteMiddleware\RouteMiddlewareInterface;
use Idealogica\RouteOne\UriGenerator\AuraUriGenerator;
use Idealogica\RouteOne\UriGenerator\UriGeneratorInterface;
use Interop\Http\Middleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface MiddlewareDispatcherInterface
 * @package Idealogica\RouteOne
 */
interface MiddlewareDispatcherInterface
{
    /**
     * @return UriGeneratorInterface|AuraUriGenerator|null
     */
    public function getUriGenerator();

    /**
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function getDefaultRoute();

    /**
     * @return array
     */
    public function getMiddlewares();

    /**
     * @param array $middlewares
     *
     * @return $this
     */
    public function setMiddlewares(array $middlewares);

    /**
     * @param MiddlewareInterface|callable $middleware
     *
     * @return MiddlewareInterface
     */
    public function addMiddleware($middleware);

    /**
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function addRoute();

    /**
     * @param string $path
     * @param MiddlewareInterface|callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function addGetRoute($path, $middleware = null);

    /**
     * @param string $path
     * @param MiddlewareInterface|callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function addPostRoute($path, $middleware = null);

    /**
     * @param string $path
     * @param MiddlewareInterface|callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function addPutRoute($path, $middleware = null);

    /**
     * @param string $path
     * @param MiddlewareInterface|callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function addDeleteRoute($path, $middleware = null);

    /**
     * @param string $path
     * @param MiddlewareInterface|callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function addHeadRoute($path, $middleware = null);

    /**
     * @param string $path
     * @param MiddlewareInterface|callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function addPatchRoute($path, $middleware = null);

    /**
     * @param string $path
     * @param MiddlewareInterface|callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function addOptionsRoute($path, $middleware = null);

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request);

    /**
     * @return $this
     */
    public function reset();
}
