<?php
namespace Idealogica\RouteOne\RouteMiddleware;

use Idealogica\RouteOne\AdapterMiddleware;
use function Idealogica\RouteOne\resetRequestRouteAttributes;
use Idealogica\RouteOne\RouteMiddleware\Exception\RouteMatchingFailedException;
use Idealogica\RouteOne\RouteMiddleware\Exception\RouteMiddlewareException;
use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\MiddlewareInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RouterMiddleware
 * @package Idealogica\RouteOne
 */
abstract class AbstractRouteMiddleware implements RouteMiddlewareInterface
{
    /**
     * @var null|MiddlewareInterface|AdapterMiddleware
     */
    protected $middleware = null;

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
     * @throws RouteMiddlewareException
     */
    public function setMiddleware($middleware)
    {
        $this->middleware = new AdapterMiddleware($middleware);
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
     * @param RequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     */
    public function process(RequestInterface $request, DelegateInterface $delegate)
    {
        /**
         * TODO: remove when middleman updates psr-15 dependency
         * @var ServerRequestInterface $request
         */
        $attributes = [];
        if ($this->getPath() !== null) {
            try {
                $attributes = $this->resolve($request);
            } catch (RouteMatchingFailedException $e) {
                return $delegate->process($request);
            }
        }
        $request = resetRequestRouteAttributes($request);
        foreach ((array)$attributes as $key => $val) {
            $request = $request->withAttribute('1.' . $key, $val);
        }
        return $this->getMiddleware()->process($request, $delegate);
    }
}
