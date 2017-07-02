<?php
namespace Idealogica\RouteOne\RouteMiddleware;

use function Idealogica\RouteOne\resetRequestRouteAttrs;
use Idealogica\RouteOne\RouteMiddleware\Exception\RouteMatchingFailedException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RouterMiddleware
 * @package Idealogica\RouteOne
 */
abstract class AbstractRouteMiddleware implements RouteMiddlewareInterface
{
    /**
     * @var null|callable
     */
    protected $middleware = null;

    /**
     * @return callable|null
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * @param callable $middleware
     *
     * @return $this
     */
    public function setMiddleware(callable $middleware)
    {
        $this->middleware = $middleware;
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
     * @param ResponseInterface $response
     * @param callable $next
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $attributes = [];
        if (!is_callable($this->getMiddleware())) {
            return $next($request, $response);
        }
        if ($this->getPath() !== null) {
            try {
                $attributes = $this->resolve($request);
            } catch (RouteMatchingFailedException $e) {
                return $next($request, $response);
            }
        }
        $request = resetRequestRouteAttrs($request);
        foreach ((array)$attributes as $key => $val) {
            $request = $request->withAttribute('1.' . $key, $val);
        }
        return $this->getMiddleware()($request, $response, $next);
    }
}
