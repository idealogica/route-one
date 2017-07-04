<?php
namespace Idealogica\RouteOne;

use Psr\Http\Message\ServerRequestInterface;

/**
 * @param ServerRequestInterface $request
 *
 * @return ServerRequestInterface
 */
function resetRequestRouteAttributes(ServerRequestInterface $request)
{
    foreach ($request->getAttributes() as $name => $value) {
        if (preg_match('#^1\.#', $name)) {
            $request = $request->withoutAttribute($name);
        }
    }
    return $request;
}