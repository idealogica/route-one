<?php
namespace Idealogica\RouteOne;

use Idealogica\RouteOne\MiddlewareDispatcher\MiddlemanMiddlewareDispatcher;
use Idealogica\RouteOne\RouteMiddleware\AuraRouteMiddleware;
use Idealogica\RouteOne\RouteMiddleware\RouteMiddlewareInterface;
use Idealogica\RouteOne\UriGenerator\AuraUriGenerator;
use Idealogica\RouteOne\UriGenerator\UriGeneratorInterface;
use Psr\Container\ContainerInterface;

/**
 * Class DispatcherFactory
 * @package Idealogica\RouteOne
 */
class DispatcherFactory
{
    /**
     * @var null|RouteMiddlewareInterface
     */
    protected $defaultRouteMiddleware = null;

    /**
     * @var null|UriGeneratorInterface
     */
    protected $uriGenerator = null;

    /**
     * @var null|callable
     */
    protected $middlewareResolver = null;

    /**
     * @param null|ContainerInterface|callable $middlewareResolver
     * @param string $basePath
     *
     * @return static
     */
    public static function createDefault($middlewareResolver = null, $basePath = '')
    {
        return new static(
            new AuraRouteMiddleware($middlewareResolver, $basePath),
            new AuraUriGenerator($basePath),
            $middlewareResolver
        );
    }

    /**
     * DispatcherFactory constructor.
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
        $this->defaultRouteMiddleware = $defaultRouteMiddleware;
        $this->uriGenerator = $uriGenerator;
        $this->middlewareResolver = $middlewareResolver;
    }

    /**
     * @param RouteMiddlewareInterface|null $defaultRouteMiddleware
     * @param UriGeneratorInterface|null $uriGenerator
     * @param null|ContainerInterface|callable $middlewareResolver
     *
     * @return MiddlemanMiddlewareDispatcher
     */
    public function createDispatcher(
        RouteMiddlewareInterface $defaultRouteMiddleware = null,
        UriGeneratorInterface $uriGenerator = null,
        $middlewareResolver = null
    ) {
        return new MiddlemanMiddlewareDispatcher(
            $defaultRouteMiddleware ?? $this->defaultRouteMiddleware,
            $uriGenerator ?? $this->uriGenerator,
            $middlewareResolver ?? $this->middlewareResolver
        );
    }
}
