<?php
namespace Idealogica\RouteOne;

use Idealogica\RouteOne\Exception\RouteOneException;
use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
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
    protected $middleware = null;

    /**
     * @var bool
     */
    protected $resetRequestRouteAttributes = false;

    /**
     * @var null|ContainerInterface|callable
     */
    protected $middlewareResolver = null;

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
     * @param RequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     * @throws RouteOneException
     */
    public function process(RequestInterface $request, DelegateInterface $delegate)
    {
        /**
         * TODO: remove when middleman updates psr-15 dependency
         * @var ServerRequestInterface $request
         */
        $middleware = $this->middleware;

        if ($this->resetRequestRouteAttributes) {
            $request = resetRequestRouteAttributes($request);
        }
        if ($middleware) {
            if ($this->middlewareResolver && is_string($middleware)) {
                $middlewareResolver = $this->middlewareResolver;
                if (is_callable($this->middlewareResolver)) {
                    $middleware = $middlewareResolver($middleware);
                } else if ($middlewareResolver instanceof ContainerInterface) {
                    $middleware = $middlewareResolver->get($middleware);
                } else {
                    throw new RouteOneException('Only PSR-11 compliant or callable middleware resolver is allowed');
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
        return $delegate->process($request);
    }
}
