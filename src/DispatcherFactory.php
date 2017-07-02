<?php
namespace Idealogica\RouteOne;

use Idealogica\RouteOne\RouteMiddleware\RouteMiddlewareInterface;
use Idealogica\RouteOne\UriGenerator\UriGeneratorInterface;

/**
 * Class DispatcherFactory
 * @package Idealogica\RouteOne
 */
class DispatcherFactory
{
    /**
     * @var string
     */
    protected $basePath = '';

    /**
     * @var null|callable
     */
    protected $middlewareResolver = null;

    /**
     * @var null|RouteMiddlewareInterface
     */
    protected $defaultRouteMiddleware = null;

    /**
     * @var null|UriGeneratorInterface
     */
    protected $uriGenerator = null;

    /**
     * DispatcherFactory constructor.
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
        $this->basePath = $basePath;
        $this->middlewareResolver = $middlewareResolver;
        $this->defaultRouteMiddleware = $defaultRouteMiddleware;
        $this->uriGenerator = $uriGenerator;
    }

    /**
     * @param string|null $basePath
     * @param callable|null $middlewareResolver
     * @param RouteMiddlewareInterface|null $defaultRouteMiddleware
     * @param UriGeneratorInterface|null $uriGenerator
     *
     * @return MiddlewareDispatcher
     */
    public function createDispatcher(
        $basePath = null,
        callable $middlewareResolver = null,
        RouteMiddlewareInterface $defaultRouteMiddleware = null,
        UriGeneratorInterface $uriGenerator = null
    ) {
        return new MiddlewareDispatcher(
            $basePath ?? $this->basePath,
            $middlewareResolver ?? $this->middlewareResolver,
            $defaultRouteMiddleware ?? $this->defaultRouteMiddleware,
            $uriGenerator ?? $this->uriGenerator
        );
    }
}
