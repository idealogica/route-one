<?php
namespace Idealogica\RouteOne\RouteMiddleware;

use Aura\Router\Map;
use Aura\Router\Matcher;
use Aura\Router\Route;
use Aura\Router\Rule\Accepts;
use Aura\Router\Rule\Allows;
use Aura\Router\Rule\Host;
use Aura\Router\Rule\Path;
use Aura\Router\Rule\RuleIterator;
use Aura\Router\Rule\Secure;
use Aura\Router\Rule\Special;
use Idealogica\RouteOne\RouteMiddleware\Exception\RouteMatchingFailedException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\NullLogger;

/**
 * Class RouterMiddleware
 * @package Idealogica\RouteOne
 */
class AuraRouteMiddleware extends AbstractRouteMiddleware
{
    /**
     * @var string
     */
    protected $basePath = '';

    /**
     * @var null|Route
     */
    protected $auraRoute = null;

    /**
     * AuraRouteMiddleware constructor.
     *
     * @param null|ContainerInterface|callable $middlewareResolver
     * @param string $basePath
     */
    public function __construct($middlewareResolver = null, $basePath = '')
    {
        parent::__construct($middlewareResolver);
        $this->basePath = $basePath;
        $this->auraRoute = new Route();
    }

    /**
     * @return Route|null
     */
    public function getAuraRoute()
    {
        return $this->auraRoute;
    }

    /**
     * @return array|null
     */
    public function getMethods()
    {
        return $this->auraRoute->allows;
    }

    /**
     * @param string $method
     *
     * @return $this
     */
    public function addMethod($method)
    {
        $this->auraRoute->allows($method);
        return $this;
    }

    /**
     * @param array $methods
     *
     * @return $this
     */
    public function setMethods(array $methods)
    {
        $this->auraRoute->allows($methods);
        return $this;
    }

    /**
     * @return array|null
     */
    public function getContentTypes()
    {
        return $this->auraRoute->accept;
    }

    /**
     * @param string $contentType
     *
     * @return $this
     */
    public function addContentType($contentType)
    {
        $this->auraRoute->accepts($contentType);
        return $this;
    }

    /**
     * Merges with the existing content types.
     *
     * @param array $contentTypes The content types.
     *
     * @return $this
     */
    public function setContentTypes(array $contentTypes)
    {
        $this->auraRoute->accepts($contentTypes);
        return $this;
    }

    /**
     * @return array|null
     */
    public function getAttributes()
    {
        return $this->auraRoute->attributes;
    }

    /**
     * @param array $attributes
     *
     * @return $this
     */
    public function setAttributes(array $attributes)
    {
        $this->auraRoute->attributes($attributes);
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getAuthData()
    {
        return $this->auraRoute->auth;
    }

    /**
     * @param mixed $auth
     *
     * @return $this
     */
    public function setAuthData($auth)
    {
        $this->auraRoute->auth($auth);
        return $this;
    }

    /**
     * @return array|null
     */
    public function getDefaults()
    {
        return $this->auraRoute->defaults;
    }

    /**
     * @param array $defaults
     *
     * @return $this
     */
    public function setDefaults(array $defaults)
    {
        $this->auraRoute->defaults($defaults);
        return $this;
    }

    /**
     * @return array|null
     */
    public function getExtras()
    {
        return $this->auraRoute->extras;
    }

    /**
     * @param array $extras
     *
     * @return $this
     */
    public function setExtras(array $extras)
    {
        $this->auraRoute->extras($extras);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHost()
    {
        return $this->auraRoute->host;
    }

    /**
     * @param string $host
     *
     * @return $this
     */
    public function setHost($host)
    {
        $this->auraRoute->host($host);
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->auraRoute->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    public function setName($name)
    {
        $this->auraRoute->name($name);
        return $this;
    }

    /**
     * @return string
     */
    public function getNamePrefix()
    {
        return $this->auraRoute->namePrefix;
    }

    /**
     * @param string $namePrefix
     *
     * @return $this
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    public function setNamePrefix($namePrefix)
    {
        $this->auraRoute->namePrefix($namePrefix);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPath()
    {
        return $this->auraRoute->path;
    }

    /**
     * @param string $path
     *
     * @return $this
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    public function setPath($path)
    {
        $this->auraRoute->path($path);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPathPrefix()
    {
        return $this->auraRoute->pathPrefix;
    }

    /**
     * @param string $pathPrefix
     *
     * @return $this
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    public function setPathPrefix($pathPrefix)
    {
        $this->auraRoute->pathPrefix($pathPrefix);
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isSecure()
    {
        return $this->auraRoute->secure;
    }

    /**
     * @param bool $secure
     *
     * @return $this
     */
    public function setSecure($secure)
    {
        $this->auraRoute->secure($secure);
        return $this;
    }

    /**
     * @return callable|null
     */
    public function getSpecial()
    {
        return $this->auraRoute->special;
    }

    /**
     * @param callable $special
     *
     * @return $this
     */
    public function setSpecial(callable $special)
    {
        $this->auraRoute->special($special);
        return $this;
    }

    /**
     * @return array|null
     */
    public function getTokens()
    {
        return $this->auraRoute->tokens;
    }

    /**
     * @param array $tokens
     *
     * @return $this
     */
    public function setTokens(array $tokens)
    {
        $this->auraRoute->tokens($tokens);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getWildcard()
    {
        return $this->auraRoute->wildcard;
    }

    /**
     * @param string $wildcard
     *
     * @return $this
     */
    public function setWildcard($wildcard)
    {
        $this->auraRoute->wildcard($wildcard);
        return $this;
    }

    /**
     * @return AuraRouteMiddleware
     */
    public function clone()
    {
        $clonedRouteMiddleware = clone $this;
        $clonedRouteMiddleware->auraRoute = clone $this->auraRoute;
        return $clonedRouteMiddleware;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return array
     * @throws RouteMatchingFailedException
     * @throws \Aura\Router\Exception\RouteAlreadyExists
     */
    protected function resolve(ServerRequestInterface $request)
    {
        $map = new Map(new Route());
        $map->addRoute($this->getAuraRoute());
        $ruleIterator = new RuleIterator([
            new Secure(),
            new Host(),
            new Path($this->basePath),
            new Allows(),
            new Accepts(),
            new Special()
        ]);
        $matcher = new Matcher($map, new NullLogger(), $ruleIterator);
        $route = $matcher->match($request);
        if (!$route) {
            throw new RouteMatchingFailedException($this->getAuraRoute()->path);
        }
        return $route->attributes;
    }
}
