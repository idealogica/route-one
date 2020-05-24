<?php
namespace Idealogica\RouteOne;

use Idealogica\RouteOne\Exception\RouteOneException;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CallableAdapterMiddleware
 * @package Idealogica\RouteOne
 */
class AdapterMiddleware implements MiddlewareInterface
{
    /**
     * @var null|callable|MiddlewareInterface
     */
    protected $middleware;

    /**
     * @var bool
     */
    protected $resetRequestRouteAttributes = false;

    /**
     * @var null|ContainerInterface|callable
     */
    protected $middlewareResolver;

    /**
     * CallableAdapterMiddleware constructor.
     *
     * @param MiddlewareInterface|callable $middleware
     * @param bool $resetRequestRouteAttributes
     * @param null|ContainerInterface|callable $middlewareResolver
     */
    public function __construct($middleware, $resetRequestRouteAttributes = false, $middlewareResolver = null)
    {
        $this->middleware = $middleware;
        $this->resetRequestRouteAttributes = $resetRequestRouteAttributes;
        $this->middlewareResolver = $middlewareResolver;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     * @throws RouteOneException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $middleware = $this->middleware;
        if ($this->resetRequestRouteAttributes) {
            $request = resetRequestRouteAttributes($request);
        }
        if ($middleware) {
            if ($this->middlewareResolver && (is_string($middleware) || is_array($middleware))) {
                if (is_array($middleware)) {
                    $id = $middleware[0];
                    $method = $middleware[1];
                } else {
                    $id = $middleware;
                    $method = '';
                }
                $middlewareResolver = $this->middlewareResolver;
                if (is_callable($this->middlewareResolver)) {
                    $instance = $middlewareResolver($id);
                } else if ($middlewareResolver instanceof ContainerInterface) {
                    $instance = $middlewareResolver->has($id) ? $middlewareResolver->get($id) : $id;
                } else {
                    throw new RouteOneException('Only PSR-11 compliant or callable middleware resolver is allowed');
                }
                if ($method) {
                    $middleware = [$instance, $method];
                } else {
                    $middleware = $instance;
                }
            }
            if (!$middleware instanceof MiddlewareInterface) {
                if (is_callable($middleware)) {
                    return $middleware($request, $delegate);
                } else {
                    throw new RouteOneException('Only PSR-15 compliant or callable middleware is allowed');
                }
            }
            return $middleware->process($request, $delegate);
        }
        return $delegate->handle($request);
    }
}
