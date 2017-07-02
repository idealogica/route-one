<?php
namespace Idealogica\RouteOne\UriGenerator;

use Aura\Router\Generator;
use Aura\Router\Map;
use Aura\Router\Route;
use Idealogica\RouteOne\RouteMiddleware\AuraRouteMiddleware;
use Idealogica\RouteOne\RouteMiddleware\RouteMiddlewareInterface;
use Idealogica\RouteOne\UriGenerator\Exception\UriGeneratorException;

/**
 * Class AuraUriGenerator
 * @package Idealogica\RouteOne\UriGenerator
 */
class AuraUriGenerator implements UriGeneratorInterface
{
    /**
     * @var string
     */
    protected $basePath = '';

    /**
     * @var null|Map
     */
    protected $auraMap = null;

    /**
     * @var null|Generator
     */
    protected $auraGenerator = null;

    /**
     * AuraUriGenerator constructor.
     *
     * @param string $basePath
     */
    public function __construct($basePath = '')
    {
        $this->basePath = $basePath;
        $this->reset();
    }

    /**
     * @param string $name
     * @param array $data
     *
     * @return false|string
     * @throws UriGeneratorException
     */
    public function generate($name, array $data = [])
    {
        try {
            return $this->refreshRouteNames()->auraGenerator->generate($name, $data);
        } catch (\Exception $e) {
            throw new UriGeneratorException($e->getMessage(), [], $e);
        }
    }

    /**
     * @param string $name
     * @param array $data
     *
     * @return false|string
     * @throws UriGeneratorException
     */
    public function generateRaw($name, array $data = [])
    {
        try {
            return $this->refreshRouteNames()->auraGenerator->generateRaw($name, $data);
        } catch (\Exception $e) {
            throw new UriGeneratorException($e->getMessage(), [], $e);
        }
    }

    /**
     * @param RouteMiddlewareInterface|AuraRouteMiddleware $routeMiddleware
     *
     * @return $this
     */
    public function addRouteMiddleware(RouteMiddlewareInterface $routeMiddleware)
    {
        $this->auraMap->addRoute($routeMiddleware->getAuraRoute());
        return $this;
    }

    /**
     * @param Route $auraRoute
     *
     * @return $this
     */
    public function addAuraRoute(Route $auraRoute)
    {
        $this->auraMap->addRoute($auraRoute);
        return $this;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->auraMap = new Map(new Route());
        $this->auraGenerator = new Generator($this->auraMap, $this->basePath);
        return $this;
    }

    /**
     * @return $this
     */
    protected function refreshRouteNames()
    {
        $routes = $this->auraMap->getRoutes();
        $this->auraMap->setRoutes([]);
        foreach($routes as $name => $route) {
            /**
             * @var Route $route
             */
            $this->auraMap->addRoute($route);
        }
        return $this;
    }
}
