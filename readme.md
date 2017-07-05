# route-one - PSR-15 route middleware for advanced middleware routing

<br /><img alt="route-one" title="route-one" src="http://storage3.static.itmages.com/i/17/0704/h_1499201379_3971410_182da2e922.png"><br /><br />

**The package is in the beta stage.**

`route-one` is a [PSR-15](https://github.com/http-interop/http-middleware) compatible middleware aimed to 
flexibly route a request to another middleware based on HTTP request url path, host, http method, etc. 
It is built on top of [Middleman](https://github.com/mindplay-dk/middleman) and [Aura.Router](https://github.com/auraphp/Aura.Router) packages.
It's a good addition to your favorite middleware dispatcher to feel it more likely classical request router.
This package also contains a middleware dispatcher that has bunch of useful methods for easy creating route middleware instances.

`route-one` is very similar to classic controller routers from every modern framework, but it has some more advantages:
- Standard compliant. You can use any PSR-15 compatible middleware. For example any of these: [middlewares/psr15-middlewares](https://github.com/middlewares/psr15-middlewares).
- Allows to build multi-dimensional routes and modify response from a group of middleware.
- It makes your code highly reusable. Any part of the web resource can be bundled as a separate packaged middleware and used 
in other projects.

## Installation
 
```composer require idealogica/route-one:~0.1.0```
 
## General usage
 
```php

// general dispatcher

$dispatcher = DispatcherFactory::CreateDefault()->createDispatcher();

// page layout middleware

$dispatcher->addMiddleware(
    function (ServerRequestInterface $request, DelegateInterface $next) {
        $response = $next->process($request);
        $content = $response->getBody()->getContents();
        return $response
            ->withBody($this->streamFor('<html><body>' . $content . '</body></html>'))
            ->withHeader('content-type', 'text/html; charset=utf-8');
    }
);

// blog middleware

$dispatcher->addMiddleware(
    function (ServerRequestInterface $request, DelegateInterface $next) {

        // blog middleware dispatcher

        $blogDispatcher = DispatcherFactory::CreateDefault()->createDispatcher();
        $blogDispatcher->getDefaultRoute()->setHost('www.test.com')->setSecure(false);

        // blog posts list middleware (path based routing)

        $blogDispatcher->addGetRoute('/blog/posts',
            function (ServerRequestInterface $request, DelegateInterface $next) {
                // stop middleware chain execution
                return new Response($this->streamFor('<h1>Posts list</h1><p>Post1</p><p>Post2</p>'));
            }
        )->setName('blog.list');

        // blog single post middleware (path based routing)

        $blogDispatcher->addGetRoute('/blog/posts/{id}',
            function (ServerRequestInterface $request, DelegateInterface $next) {
                $id = (int)$request->getAttribute('1.id'); // prefix for route-one attributes
                // post id is valid
                if ($id === 1) {
                    // stop middleware chain execution
                    return new Response($this->streamFor(sprintf('<h1>Post #%s</h1><p>Example post</p>', $id)));
                }
                // post not found, continue to the next middleware
                return $next->process($request);
            }
        )->setName('blog.post');

        // blog page not found middleware (no routing, executes for each request)

        $blogDispatcher->addMiddleware(
            function (ServerRequestInterface $request, DelegateInterface $next)
            {
                // 404 response
                return new Response($this->streamFor('<h1>Page not found</h1>'), 404);
            }
        );

        return $blogDispatcher->dispatch($request);
    }
);

// dispatching

$response = $dispatcher->dispatch(
    new ServerRequest(
        [],
        [],
        'http://www.test.com/blog/posts/1',
        'GET'
    )
);

(new SapiEmitter())->emit($response);

``` 

As you can see you can use callable as middleware. It is not a part of PSR-15 but it can be very 
helpful for quick prototyping.

Also you can use the route middleware separately with your own dispatcher:

```php

$routeFactory = new RouteFactory(
    new AuraRouteMiddleware($basePath),
    new AuraUriGenerator($basePath)
);
$routeMiddleware = $routeFactory->createGetRoute('/blog/posts', $middlewareToRoute);

```

For more information on routing rules configuration please refer to 
[Aura.Router routes documentation](https://github.com/auraphp/Aura.Router/blob/3.x/docs/defining-routes.md).

## Uri generation

You can use generator to create URIs based or your named routes. Example for the code above:

```php
// route path is '/blog/posts/{id}' for 'blog.post' route
$blogPostUrl = $blogDispatcher->getUriGenerator()->generate('blog.post', ['id' => 100]);
echo($blogPostUrl); // outputs "http://www.test.com/blog/posts/100"
```

For more information on URIs generation please refer to 
[Aura.Router generator documentation](https://github.com/auraphp/Aura.Router/blob/3.x/docs/generating-paths.md).

## License

route-one is licensed under a [MIT License](https://opensource.org/licenses/MIT).
