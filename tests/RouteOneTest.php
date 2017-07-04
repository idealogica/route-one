<?php
use Idealogica\RouteOne\DispatcherFactory;
use Idealogica\RouteOne\MiddlewareDispatcher\MiddlemanMiddlewareDispatcher;
use Idealogica\RouteOne\UriGenerator\Exception\UriGeneratorException;
use Interop\Http\Middleware\DelegateInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;
use Zend\Diactoros\Response\SapiEmitter;

/**
 * Class RouteOneTest
 */
class RouteOneTest extends TestCase
{
    /**
     * @var null|DispatcherFactory
     */
    protected $dispatcherFactory = null;

    /**
     *
     */
    public function setUp()
    {
        $this->dispatcherFactory = DispatcherFactory::CreateDefault();
    }

    /**
     *
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
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $content = $response->getBody()->getContents();
        $this->assertContains('Posts list', $content);
        $this->assertContains('<html><body>', $content);
        $contentTypeHeader = $response->getHeaderLine('content-type');
        $this->assertContains('text/html', $contentTypeHeader);
        $statusCode = $response->getStatusCode();
        $this->assertEquals(200, $statusCode);
    }

    /**
     *
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
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $content = $response->getBody()->getContents();
        $this->assertContains('Post #1', $content);
        $this->assertContains('<html><body>', $content);
        $contentTypeHeader = $response->getHeaderLine('content-type');
        $this->assertContains('text/html', $contentTypeHeader);
        $statusCode = $response->getStatusCode();
        $this->assertEquals(200, $statusCode);
    }

    /**
     *
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
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $content = $response->getBody()->getContents();
        $this->assertContains('Page not found', $content);
        $this->assertContains('<html><body>', $content);
        $contentTypeHeader = $response->getHeaderLine('content-type');
        $this->assertContains('text/html', $contentTypeHeader);
        $statusCode = $response->getStatusCode();
        $this->assertEquals(404, $statusCode);
    }

    /**
     *
     */
    public function testUriGenerator()
    {
        /**
         * @var MiddlemanMiddlewareDispatcher $blogDispatcher
         */
        $this->setupDispatcher($blogDispatcher);
        $blogUrl = $blogDispatcher->getUriGenerator()->generate('blog.list');
        $blogPostUrl = $blogDispatcher->getUriGenerator()->generate('blog.post', ['id' => 100]);
        $this->assertEquals('http://www.test.com/blog/posts', $blogUrl);
        $this->assertEquals('http://www.test.com/blog/posts/100', $blogPostUrl);
    }

    /**
     *
     */
    public function testReset()
    {
        /**
         * @var MiddlemanMiddlewareDispatcher $blogDispatcher
         */
        $this->setupDispatcher($blogDispatcher);
        $blogDispatcher->reset();
        $this->assertEmpty($blogDispatcher->getMiddlewares());
        try {
            $blogDispatcher->getUriGenerator()->generate('blog.list');
        } catch (UriGeneratorException $e) {
            return;
        }
        $this->fail();
    }

    /**
     * @param MiddlemanMiddlewareDispatcher|null $blogDispatcher
     *
     * @return MiddlemanMiddlewareDispatcher
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
                $this->assertArrayHasKey('1.id', $request->getAttributes());
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

    /**
     *
     */
    protected function testDocExampleCode()
    {
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

        // (new SapiEmitter())->emit($response);

        $this->assertContains('Post #1', $response->getBody()->getContents());
    }
}
