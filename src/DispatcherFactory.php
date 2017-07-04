<?php
namespace Idealogica\RouteOne;

use Idealogica\RouteOne\MiddlewareDispatcher\MiddlemanMiddlewareDispatcher;
use Idealogica\RouteOne\RouteMiddleware\AuraRouteMiddleware;
use Idealogica\RouteOne\RouteMiddleware\RouteMiddlewareInterface;
use Idealogica\RouteOne\UriGenerator\AuraUriGenerator;
use Idealogica\RouteOne\UriGenerator\UriGeneratorInterface;

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
     * @param string $basePath
     * @param callable|null $middlewareResolver
     *
     * @return static
     */
    public static function CreateDefault($basePath = '', callable $middlewareResolver = null)
    {
        return new static(
            new AuraRouteMiddleware($basePath),
            new AuraUriGenerator($basePath),
            $middlewareResolver
        );
    }

    /**
     * DispatcherFactory constructor.
     *
     * @param RouteMiddlewareInterface $defaultRouteMiddleware
     * @param UriGeneratorInterface $uriGenerator
     * @param callable|null $middlewareResolver
     */
    public function __construct(
        RouteMiddlewareInterface $defaultRouteMiddleware,
        UriGeneratorInterface $uriGenerator,
        callable $middlewareResolver = null
    ) {
        $this->defaultRouteMiddleware = $defaultRouteMiddleware;
        $this->uriGenerator = $uriGenerator;
        $this->middlewareResolver = $middlewareResolver;
    }

    /**
     * @param RouteMiddlewareInterface|null $defaultRouteMiddleware
     * @param UriGeneratorInterface|null $uriGenerator
     * @param callable|null $middlewareResolver
     *
     * @return MiddlemanMiddlewareDispatcher
     */
    public function createDispatcher(
        RouteMiddlewareInterface $defaultRouteMiddleware = null,
        UriGeneratorInterface $uriGenerator = null,
        callable $middlewareResolver = null
    ) {
        return new MiddlemanMiddlewareDispatcher(
            $defaultRouteMiddleware ?? $this->defaultRouteMiddleware,
            $uriGenerator ?? $this->uriGenerator,
            $middlewareResolver ?? $this->middlewareResolver
        );
    }
}
