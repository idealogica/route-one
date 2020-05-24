<?php
namespace Idealogica\RouteOne\RouteMiddleware;

use Psr\Http\Server\MiddlewareInterface;

/**
 * Class RouterMiddlewareInterface
 * @package Idealogica\RouteOne
 */
interface RouteMiddlewareInterface extends MiddlewareInterface
{
    const METHOD_GET = 'GET';

    const METHOD_POST = 'POST';

    const METHOD_PUT = 'PUT';

    const METHOD_DELETE = 'DELETE';

    const METHOD_HEAD = 'HEAD';

    const METHOD_PATCH = 'PATCH';

    const METHOD_OPTIONS = 'OPTIONS';

    /**
     * @return array|null
     */
    public function getMethods();

    /**
     * @param string $method
     *
     * @return $this
     */
    public function addMethod($method);

    /**
     * @param array $methods
     *
     * @return $this
     */
    public function setMethods(array $methods);

    /**
     * @return string|null
     */
    public function getPath();

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setPath($path);

    /**
     * @return callable|null
     */
    public function getMiddleware();

    /**
     * @param MiddlewareInterface|callable $middleware
     *
     * @return $this
     */
    public function setMiddleware($middleware);

    /**
     * @return $this
     */
    public function clone();
}
