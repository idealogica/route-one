<?php
namespace Idealogica\RouteOne\RouteMiddleware;

use Idealogica\RouteOne\AdapterMiddleware;
use function Idealogica\RouteOne\resetRequestRouteAttributes;
use Idealogica\RouteOne\RouteMiddleware\Exception\RouteMatchingFailedException;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RouterMiddleware
 * @package Idealogica\RouteOne
 */
abstract class AbstractRouteMiddleware implements RouteMiddlewareInterface
{
    /**
     * @var null|ContainerInterface|callable
     */
    protected $middlewareResolver;

    /**
     * @var null|MiddlewareInterface|AdapterMiddleware
     */
    protected $middleware;

    /**
     * AbstractRouteMiddleware constructor.
     *
     * @param null|ContainerInterface|callable $middlewareResolver
     */
    public function __construct($middlewareResolver = null)
    {
        $this->middlewareResolver = $middlewareResolver;
    }

    /**
     * @return MiddlewareInterface|null|AdapterMiddleware
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * @param MiddlewareInterface|callable $middleware
     *
     * @return $this
     */
    public function setMiddleware($middleware)
    {
        $this->middleware = new AdapterMiddleware($middleware, false, $this->middlewareResolver);
        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return array
     * @throws RouteMatchingFailedException
     */
    abstract protected function resolve(ServerRequestInterface $request);

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     * @throws \Idealogica\RouteOne\Exception\RouteOneException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        try {
            $attributes = $this->resolve($request);
        } catch (RouteMatchingFailedException $e) {
            return $delegate->handle($request);
        }
        $request = resetRequestRouteAttributes($request);
        foreach ((array)$attributes as $key => $val) {
            $request = $request->withAttribute('1.' . $key, $val);
        }
        return $this->getMiddleware()->process($request, $delegate);
    }
}
