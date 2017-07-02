<?php
namespace Idealogica\RouteOne;

use Idealogica\RouteOne\RouteMiddleware\AuraRouteMiddleware;
use Idealogica\RouteOne\RouteMiddleware\RouteMiddlewareInterface;
use Idealogica\RouteOne\UriGenerator\AuraUriGenerator;
use Idealogica\RouteOne\UriGenerator\UriGeneratorInterface;

/**
 * Class RouterFactory
 * @package Idealogica\RouteOne
 */
class RouteFactory
{
    /**
     * @var null|UriGeneratorInterface
     */
    protected $uriGenerator = null;

    /**
     * @var null|RouteMiddlewareInterface
     */
    protected $defaultRouteMiddleware = null;

    /**
     * RouterFactory constructor.
     *
     * @param string $basePath
     * @param RouteMiddlewareInterface|null $defaultRouteMiddleware
     * @param UriGeneratorInterface|null $uriGenerator
     */
    public function __construct(
        $basePath = '',
        RouteMiddlewareInterface $defaultRouteMiddleware = null,
        UriGeneratorInterface $uriGenerator = null
    ) {
        if ($defaultRouteMiddleware) {
            $this->defaultRouteMiddleware = $defaultRouteMiddleware;
        } else {
            $this->defaultRouteMiddleware = new AuraRouteMiddleware($basePath);
        }
        if ($uriGenerator) {
            $this->uriGenerator = $uriGenerator;
        } else if ($this->defaultRouteMiddleware instanceof AuraRouteMiddleware) {
            $this->uriGenerator = new AuraUriGenerator($basePath);
        }
    }

    /**
     * @return RouteMiddlewareInterface
     */
    public function getDefaultRoute()
    {
        return $this->defaultRouteMiddleware;
    }

    /**
     * @return AuraRouteMiddleware|RouteMiddlewareInterface
     */
    public function createRoute()
    {
        $routeMiddleware = $this->defaultRouteMiddleware->clone();
        $generator = $this->getUriGenerator();
        if ($generator) {
            $generator->addRouteMiddleware($routeMiddleware);
        }
        return $routeMiddleware;
    }

    /**
     * @param string $path
     * @param callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function createGetRoute($path, callable $middleware = null)
    {
        return $this->createRoute()
            ->addMethod(RouteMiddlewareInterface::METHOD_GET)
            ->setPath($path)
            ->setMiddleware($middleware);
    }

    /**
     * @param string $path
     * @param callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function createPostRoute($path, callable $middleware = null)
    {
        return $this->createRoute()
            ->addMethod(RouteMiddlewareInterface::METHOD_POST)
            ->setPath($path)
            ->setMiddleware($middleware);
    }

    /**
     * @param string $path
     * @param callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function createPutRoute($path, callable $middleware = null)
    {
        return $this->createRoute()
            ->addMethod(RouteMiddlewareInterface::METHOD_PUT)
            ->setPath($path)
            ->setMiddleware($middleware);
    }

    /**
     * @param string $path
     * @param callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function createDeleteRoute($path, callable $middleware = null)
    {
        return $this->createRoute()
            ->addMethod(RouteMiddlewareInterface::METHOD_DELETE)
            ->setPath($path)
            ->setMiddleware($middleware);
    }

    /**
     * @param string $path
     * @param callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function createHeadRoute($path, callable $middleware = null)
    {
        return $this->createRoute()
            ->addMethod(RouteMiddlewareInterface::METHOD_HEAD)
            ->setPath($path)
            ->setMiddleware($middleware);
    }

    /**
     * @param string $path
     * @param callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function createPatchRoute($path, callable $middleware = null)
    {
        return $this->createRoute()
            ->addMethod(RouteMiddlewareInterface::METHOD_PATCH)
            ->setPath($path)
            ->setMiddleware($middleware);
    }

    /**
     * @param string $path
     * @param callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     */
    public function createOptionsRoute($path, callable $middleware = null)
    {
        return $this->createRoute()
            ->addMethod(RouteMiddlewareInterface::METHOD_OPTIONS)
            ->setPath($path)
            ->setMiddleware($middleware);
    }

    /**
     * @return UriGeneratorInterface|AuraUriGenerator|null
     */
    public function getUriGenerator()
    {
        return $this->uriGenerator;
    }
}
