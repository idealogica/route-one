<?php
use Idealogica\RouteOne\DispatcherFactory;
use Idealogica\RouteOne\MiddlewareDispatcher\MiddlemanMiddlewareDispatcher;
use Idealogica\RouteOne\UriGenerator\Exception\UriGeneratorException;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;

/**
 * Class RouteOneTest
 */
class RouteOneTest extends TestCase
{
    /**
     * @var null|DispatcherFactory
     */
    protected $dispatcherFactory;

    /**
     *
     */
    public function setUp()
    {
        $this->dispatcherFactory = DispatcherFactory::createDefault();
    }

    /**
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    public function testBlogRoute()
    {
        $response = $this->setupDispatcher()->dispatch(
            new ServerRequest(
                [],
                [],
                'http://www.test.com/blog/posts',
                'GET'
            )
        );
        self::assertInstanceOf(ResponseInterface::class, $response);
        $content = $response->getBody()->getContents();
        self::assertStringContainsString('Posts list', $content);
        self::assertStringContainsString('<html><body>', $content);
        $contentTypeHeader = $response->getHeaderLine('content-type');
        self::assertStringContainsString('text/html', $contentTypeHeader);
        $statusCode = $response->getStatusCode();
        self::assertEquals(200, $statusCode);
    }

    /**
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    public function testBlogPostRoute()
    {
        $response = $this->setupDispatcher()->dispatch(
            new ServerRequest(
                [],
                [],
                'http://www.test.com/blog/posts/1',
                'GET'
            )
        );
        self::assertInstanceOf(ResponseInterface::class, $response);
        $content = $response->getBody()->getContents();
        self::assertStringContainsString('Post #1', $content);
        self::assertStringContainsString('<html><body>', $content);
        $contentTypeHeader = $response->getHeaderLine('content-type');
        self::assertStringContainsString('text/html', $contentTypeHeader);
        $statusCode = $response->getStatusCode();
        self::assertEquals(200, $statusCode);
    }

    /**
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    public function test404Error()
    {
        $response = $this->setupDispatcher()->dispatch(
            new ServerRequest(
                [],
                [],
                'http://www.test.com/blog/posts/123456',
                'GET'
            )
        );
        self::assertInstanceOf(ResponseInterface::class, $response);
        $content = $response->getBody()->getContents();
        self::assertStringContainsString('Page not found', $content);
        self::assertStringContainsString('<html><body>', $content);
        $contentTypeHeader = $response->getHeaderLine('content-type');
        self::assertStringContainsString('text/html', $contentTypeHeader);
        $statusCode = $response->getStatusCode();
        self::assertEquals(404, $statusCode);
    }

    /**
     * @throws UriGeneratorException
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    public function testUriGenerator()
    {
        /**
         * @var MiddlemanMiddlewareDispatcher $blogDispatcher
         */
        $this->setupDispatcher($blogDispatcher);
        $blogUrl = $blogDispatcher->getUriGenerator()->generate('blog.list');
        $blogPostUrl = $blogDispatcher->getUriGenerator()->generate('blog.post', ['id' => 100]);
        self::assertEquals('http://www.test.com/blog/posts', $blogUrl);
        self::assertEquals('http://www.test.com/blog/posts/100', $blogPostUrl);
    }

    /**
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    public function testReset()
    {
        /**
         * @var MiddlemanMiddlewareDispatcher $blogDispatcher
         */
        $this->setupDispatcher($blogDispatcher);
        $blogDispatcher->reset();
        self::assertEmpty($blogDispatcher->getMiddlewares());
        try {
            $blogDispatcher->getUriGenerator()->generate('blog.list');
        } catch (UriGeneratorException $e) {
            return;
        }
        $this->fail();
    }

    /**
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    public function testMiddlewareResolver()
    {
        $resolver = function ($id) {
            if ($id === 'middleware2') {
                return function (ServerRequestInterface $request, DelegateInterface $next) use ($id) {
                    return new Response($this->streamFor($id));
                };
            } elseif ($id === 'middleware1') {
                return function (ServerRequestInterface $request, DelegateInterface $next) use ($id) {
                    $response = $next->handle($request);
                    return $response->withBody($this->streamFor($response->getBody()->getContents() . $id));
                };
            }
            $this->fail();
            return null;
        };
        $dispatcher = DispatcherFactory::createDefault($resolver)->createDispatcher();
        $dispatcher->addMiddleware('middleware1');
        $dispatcher->addGetRoute('/', 'middleware2');
        $response = $dispatcher->dispatch(
            new ServerRequest(
                [],
                [],
                'http://www.test.com/',
                'GET'
            )
        );
        self::assertInstanceOf(ResponseInterface::class, $response);
        $content = $response->getBody()->getContents();
        self::assertStringContainsString('middleware2middleware1', $content);
    }

    /**
     *
     */
    public function testDocExampleCode()
    {
        // general dispatcher

        $dispatcher = DispatcherFactory::CreateDefault()->createDispatcher();

        // page layout middleware

        $dispatcher->addMiddleware(
            function (ServerRequestInterface $request, DelegateInterface $next) {
                $response = $next->handle($request);
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
                        return $next->handle($request);
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

        // (new SapiEmitter())->emit($response);

        self::assertStringContainsString('Post #1', $response->getBody()->getContents());
    }

    /**
     * @param MiddlemanMiddlewareDispatcher|null $blogDispatcher
     *
     * @return MiddlemanMiddlewareDispatcher
     * @throws \Aura\Router\Exception\ImmutableProperty
     */
    protected function setupDispatcher(MiddlemanMiddlewareDispatcher &$blogDispatcher = null)
    {
        // general dispatcher

        $dispatcher = $this->dispatcherFactory->createDispatcher();

        // blog dispatcher

        $blogDispatcher = $this->dispatcherFactory->createDispatcher();

        // blog dispatcher default route params

        $blogDispatcher->getDefaultRoute()->setHost('www.test.com')->setSecure(false);

        // blog posts lists middleware (path based routing)

        $blogDispatcher->addGetRoute('/blog/posts',
            function (ServerRequestInterface $request, DelegateInterface $next) {
                // stop middleware chain execution
                return new Response($this->streamFor('<h1>Posts list</h1><p>Post1</p><p>Post2</p>'));
            }
        )->setName('blog.list');

        // blog post middleware (path based routing)

        $blogDispatcher->addGetRoute('/blog/posts/{id}',
            function (ServerRequestInterface $request, DelegateInterface $next) {
                self::assertArrayHasKey('1.id', $request->getAttributes());
                $id = (int)$request->getAttribute('1.id'); // prefix for route-one attributes
                // post id is valid
                if ($id === 1) {
                    // stop middleware chain execution
                    return new Response($this->streamFor(sprintf('<h1>Post #%s</h1><p>Example post</p>', $id)));
                }
                // post not found, continue to the next middleware
                return $next->handle($request);
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

        // page layout middleware

        $dispatcher->addMiddleware(
            function (ServerRequestInterface $request, DelegateInterface $next) {
                $response = $next->handle($request);
                $content = $response->getBody()->getContents();
                return $response
                    ->withBody($this->streamFor('<html><body>' . $content . '</body></html>'))
                    ->withHeader('content-type', 'text/html; charset=utf-8');
            }
        );

        // blog middleware

        $dispatcher->addMiddleware(
            function (ServerRequestInterface $request, DelegateInterface $next) use ($blogDispatcher) {
                return $blogDispatcher->dispatch($request);
            }
        );

        return $dispatcher;
    }

    /**
     * @param string $content
     *
     * @return Stream
     */
    protected function streamFor($content)
    {
        $body = new Stream('php://temp', 'wb+');
        $body->write($content);
        $body->rewind();
        return $body;
    }
}
