<?php
namespace Idealogica\RouteOne;

use Idealogica\RouteOne\RouteMiddleware\AuraRouteMiddleware;
use Idealogica\RouteOne\RouteMiddleware\RouteMiddlewareInterface;
use Idealogica\RouteOne\UriGenerator\AuraUriGenerator;
use Idealogica\RouteOne\UriGenerator\UriGeneratorInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Class RouterFactory
 * @package Idealogica\RouteOne
 */
class RouteFactory
{
    /**
     * @var null|RouteMiddlewareInterface
     */
    protected $defaultRouteMiddleware;

    /**
     * @var null|UriGeneratorInterface
     */
    protected $uriGenerator;

    /**
     * RouterFactory constructor.
     *
     * @param RouteMiddlewareInterface $defaultRouteMiddleware
     * @param UriGeneratorInterface $uriGenerator
     */
    public function __construct(
        RouteMiddlewareInterface $defaultRouteMiddleware,
        UriGeneratorInterface $uriGenerator
    ) {
        $this->defaultRouteMiddleware = $defaultRouteMiddleware;
        $this->uriGenerator = $uriGenerator;
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
     * @param MiddlewareInterface|callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    public function createGetRoute($path, $middleware = null)
    {
        return $this->createRoute()
            ->addMethod(RouteMiddlewareInterface::METHOD_GET)
            ->setPath($path)
            ->setMiddleware($middleware);
    }

    /**
     * @param string $path
     * @param MiddlewareInterface|callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    public function createPostRoute($path, $middleware = null)
    {
        return $this->createRoute()
            ->addMethod(RouteMiddlewareInterface::METHOD_POST)
            ->setPath($path)
            ->setMiddleware($middleware);
    }

    /**
     * @param string $path
     * @param MiddlewareInterface|callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    public function createPutRoute($path, $middleware = null)
    {
        return $this->createRoute()
            ->addMethod(RouteMiddlewareInterface::METHOD_PUT)
            ->setPath($path)
            ->setMiddleware($middleware);
    }

    /**
     * @param string $path
     * @param MiddlewareInterface|callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    public function createDeleteRoute($path, $middleware = null)
    {
        return $this->createRoute()
            ->addMethod(RouteMiddlewareInterface::METHOD_DELETE)
            ->setPath($path)
            ->setMiddleware($middleware);
    }

    /**
     * @param string $path
     * @param MiddlewareInterface|callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    public function createHeadRoute($path, $middleware = null)
    {
        return $this->createRoute()
            ->addMethod(RouteMiddlewareInterface::METHOD_HEAD)
            ->setPath($path)
            ->setMiddleware($middleware);
    }

    /**
     * @param string $path
     * @param MiddlewareInterface|callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    public function createPatchRoute($path, $middleware = null)
    {
        return $this->createRoute()
            ->addMethod(RouteMiddlewareInterface::METHOD_PATCH)
            ->setPath($path)
            ->setMiddleware($middleware);
    }

    /**
     * @param string $path
     * @param MiddlewareInterface|callable $middleware
     *
     * @return RouteMiddlewareInterface|AuraRouteMiddleware
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    public function createOptionsRoute($path, $middleware = null)
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
