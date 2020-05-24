<?php
namespace Idealogica\RouteOne\MiddlewareDispatcher;

use Idealogica\RouteOne\AdapterMiddleware;
use Idealogica\RouteOne\RouteFactory;
use Idealogica\RouteOne\RouteMiddleware\AuraRouteMiddleware;
use Idealogica\RouteOne\RouteMiddleware\RouteMiddlewareInterface;
use Idealogica\RouteOne\UriGenerator\AuraUriGenerator;
use Idealogica\RouteOne\UriGenerator\UriGeneratorInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Container\ContainerInterface;

/**
 * Class AbstractMiddlewareDispatcher
 * @package Idealogica\RouteOne\Dispatcher
 */
abstract class AbstractMiddlewareDispatcher implements MiddlewareDispatcherInterface
{
    /**
     * @var null|ContainerInterface|callable
     */
    protected $middlewareResolver;

    /**
     * @var null|RouteFactory
     */
    protected $routeFactory;

    /**
     * @var array
     */
    protected $middlewares = [];

    /**
     * MiddlewareDispatcher constructor.
     *
     * @param RouteMiddlewareInterface $defaultRouteMiddleware
     * @param UriGeneratorInterface $uriGenerator
     * @param null|ContainerInterface|callable $middlewareResolver
     */
    public function __construct(
        RouteMiddlewareInterface $defaultRouteMiddleware,
        UriGeneratorInterface $uriGenerator,
        $middlewareResolver = null
    ) {
        $this->routeFactory = new RouteFactory(
            $defaultRouteMiddleware,
            $uriGenerator
        );
        $this->middlewareResolver = $middlewareResolver;
    }

    /**
     * @return UriGeneratorInterface|AuraUriGenerator|null
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
     * @param MiddlewareInterface|callable $middleware
     *
     * @return MiddlewareInterface
     */
    public function addMiddleware($middleware)
    {
        $this->middlewares[] = new AdapterMiddleware($middleware, true, $this->middlewareResolver);
        return $middleware;
    }

    /**
     * @return MiddlewareInterface|RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function addRoute()
    {
        return $this->addMiddleware(
            $this->routeFactory->createRoute()
        );
    }

    /**
     * @param string $path
     * @param MiddlewareInterface|callable $middleware
     *
     * @return MiddlewareInterface|RouteMiddlewareInterface|AuraRouteMiddleware
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    public function addGetRoute($path, $middleware = null)
    {
        return $this->addMiddleware(
            $this->routeFactory->createGetRoute($path, $middleware)
        );
    }

    /**
     * @param string $path
     * @param MiddlewareInterface|callable $middleware
     *
     * @return MiddlewareInterface|RouteMiddlewareInterface|AuraRouteMiddleware
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    public function addPostRoute($path, $middleware = null)
    {
        return $this->addMiddleware(
            $this->routeFactory->createPostRoute($path, $middleware)
        );
    }

    /**
     * @param string $path
     * @param MiddlewareInterface|callable $middleware
     *
     * @return MiddlewareInterface|RouteMiddlewareInterface|AuraRouteMiddleware
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    public function addPutRoute($path, $middleware = null)
    {
        return $this->addMiddleware(
            $this->routeFactory->createPutRoute($path, $middleware)
        );
    }

    /**
     * @param string $path
     * @param MiddlewareInterface|callable $middleware
     *
     * @return MiddlewareInterface|RouteMiddlewareInterface|AuraRouteMiddleware
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    public function addDeleteRoute($path, $middleware = null)
    {
        return $this->addMiddleware(
            $this->routeFactory->createDeleteRoute($path, $middleware)
        );
    }

    /**
     * @param string $path
     * @param MiddlewareInterface|callable $middleware
     *
     * @return MiddlewareInterface|RouteMiddlewareInterface|AuraRouteMiddleware
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    public function addHeadRoute($path, $middleware = null)
    {
        return $this->addMiddleware(
            $this->routeFactory->createHeadRoute($path, $middleware)
        );
    }

    /**
     * @param string $path
     * @param MiddlewareInterface|callable $middleware
     *
     * @return MiddlewareInterface|RouteMiddlewareInterface|AuraRouteMiddleware
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    public function addPatchRoute($path, $middleware = null)
    {
        return $this->addMiddleware(
            $this->routeFactory->createPatchRoute($path, $middleware)
        );
    }

    /**
     * @param string $path
     * @param MiddlewareInterface|callable $middleware
     *
     * @return MiddlewareInterface|RouteMiddlewareInterface|AuraRouteMiddleware
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    public function addOptionsRoute($path, $middleware = null)
    {
        return $this->addMiddleware(
            $this->routeFactory->createOptionsRoute($path, $middleware)
        );
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
