<?php
namespace Idealogica\RouteOne\UriGenerator;

use Idealogica\RouteOne\RouteMiddleware\RouteMiddlewareInterface;

/**
 * Interface UriGeneratorInterface
 * @package Idealogica\RouteOne\UriGenerator
 */
interface UriGeneratorInterface
{
    /**
     * @param string $name
     * @param array $data
     *
     * @return false|string
     */
    public function generate($name, array $data = []);

    /**
     * @param string $name
     * @param array $data
     *
     * @return false|string
     */
    public function generateRaw($name, array $data = []);

    /**
     * @param RouteMiddlewareInterface $routeMiddleware
     *
     * @return $this
     */
    public function addRouteMiddleware(RouteMiddlewareInterface $routeMiddleware);

    /**
     * @return $this
     */
    public function reset();
}
