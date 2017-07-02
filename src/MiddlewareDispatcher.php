<?php
namespace Idealogica\RouteOne;

use Idealogica\RouteOne\RouteMiddleware\AuraRouteMiddleware;
use Idealogica\RouteOne\RouteMiddleware\RouteMiddlewareInterface;
use Idealogica\RouteOne\UriGenerator\AuraUriGenerator;
use Idealogica\RouteOne\UriGenerator\UriGeneratorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Relay\RelayBuilder;

/**
 * Class MiddlewareDispatcher
 * @package Idealogica\RouteOne
 */
class MiddlewareDispatcher
{
    /**
     * @var null|RelayBuilder
     */
    protected $relayBuilder = null;

    /**
     * @var null|RouteFactory
     */
    protected $routeFactory = null;

    /**
     * @var array
     */
    protected $middlewares = [];

    /**
     * MiddlewareDispatcher constructor.
     *
     * @param string $basePath
     * @param callable|null $middlewareResolver
     * @param RouteMiddlewareInterface|null $defaultRouteMiddleware
     * @param UriGeneratorInterface|null $uriGenerator
     */
    public function __construct(
        $basePath = '',
        callable $middlewareResolver = null,
        RouteMiddlewareInterface $defaultRouteMiddleware = null,
        UriGeneratorInterface $uriGenerator = null
    ) {
        $this->relayBuilder = new RelayBuilder($middlewareResolver);
        $this->routeFactory = new RouteFactory(
            $basePath,
            $defaultRouteMiddleware,
            $uriGenerator
        );
    }

    /**
     * @return UriGenerator\UriGeneratorInterface|AuraUriGenerator|null
     */
    public function getUriGenerator()
    {
        return $this->routeFactory->getUriGenerator();
    }

    /**
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function getDefaultRoute()
    {
        return $this->routeFactory->getDefaultRoute();
    }

    /**
     * @return array
     */
    public function getMiddlewares()
    {
        return $this->middlewares;
    }

    /**
     * @param array $middlewares
     *
     * @return $this
     */
    public function setMiddlewares(array $middlewares)
    {
        foreach ($middlewares as $middleware) {
            $this->addMiddleware($middleware);
        }
        return $this;
    }

    /**
     * @param callable $middleware
     *
     * @return callable
     */
    public function addMiddleware(callable $middleware)
    {
        $this->middlewares[] =
            function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use ($middleware) {
                return $middleware(resetRequestRouteAttrs($request), $response, $next);
            };
        return $middleware;
    }

    /**
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function addRoute()
    {
        return $this->addMiddleware(
            $this->routeFactory->createRoute()
        );
    }

    /**
     * @param string $path
     * @param callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function addGetRoute($path, callable $middleware = null)
    {
        return $this->addMiddleware(
            $this->routeFactory->createGetRoute($path, $middleware)
        );
    }

    /**
     * @param string $path
     * @param callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function addPostRoute($path, callable $middleware = null)
    {
        return $this->addMiddleware(
            $this->routeFactory->createPostRoute($path, $middleware)
        );
    }

    /**
     * @param string $path
     * @param callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function addPutRoute($path, callable $middleware = null)
    {
        return $this->addMiddleware(
            $this->routeFactory->createPutRoute($path, $middleware)
        );
    }

    /**
     * @param string $path
     * @param callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function addDeleteRoute($path, callable $middleware = null)
    {
        return $this->addMiddleware(
            $this->routeFactory->createDeleteRoute($path, $middleware)
        );
    }

    /**
     * @param string $path
     * @param callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function addHeadRoute($path, callable $middleware = null)
    {
        return $this->addMiddleware(
            $this->routeFactory->createHeadRoute($path, $middleware)
        );
    }

    /**
     * @param string $path
     * @param callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function addPatchRoute($path, callable $middleware = null)
    {
        return $this->addMiddleware(
            $this->routeFactory->createPatchRoute($path, $middleware)
        );
    }

    /**
     * @param string $path
     * @param callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function addOptionsRoute($path, callable $middleware = null)
    {
        return $this->addMiddleware(
            $this->routeFactory->createOptionsRoute($path, $middleware)
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request, ResponseInterface $response)
    {
        $relay = $this->relayBuilder->newInstance($this->middlewares);
        return $relay($request, $response);
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->middlewares = [];
        $this->getUriGenerator()->reset();
        return $this;
    }
}
