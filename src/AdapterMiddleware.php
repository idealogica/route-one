<?php
namespace Idealogica\RouteOne;

use Idealogica\RouteOne\Exception\RouteOneException;
use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\MiddlewareInterface;
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
     * CallableAdapterMiddleware constructor.
     *
     * @param MiddlewareInterface|callable $middleware
     * @param bool $resetRequestRouteAttributes
     */
    public function __construct($middleware, $resetRequestRouteAttributes = false)
    {
        $this->middleware = $middleware;
        $this->resetRequestRouteAttributes = $resetRequestRouteAttributes;
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
            $request = resetRequestRouteAttrs($request);
        }
        if ($middleware) {
            if (!$middleware instanceof MiddlewareInterface) {
                if (is_callable($middleware)) {
                    return $middleware($request, $delegate);
                } else {
                    throw new RouteOneException('Only PSR-15 and callable middleware is allowed');
                }
            }
            return $middleware->process($request, $delegate);
        }
        return $delegate->process($request);
    }
}
