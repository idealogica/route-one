<?php
use Idealogica\RouteOne\DispatcherFactory;
use Idealogica\RouteOne\MiddlewareDispatcher;
use Idealogica\RouteOne\UriGenerator\Exception\UriGeneratorException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;

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
        $this->dispatcherFactory = new DispatcherFactory();
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
                'http://www.test.com/blog',
                'GET'
            ),
            new Response()
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
                'http://www.test.com/blog/1',
                'GET'
            ),
            new Response()
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
                'http://www.test.com/blog/123456',
                'GET'
            ),
            new Response()
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
         * @var MiddlewareDispatcher $blogDispatcher
         */
        $this->setupDispatcher($blogDispatcher);
        $blogUrl = $blogDispatcher->getUriGenerator()->generate('blog.list');
        $blogPostUrl = $blogDispatcher->getUriGenerator()->generate('blog.post', ['id' => 100]);
        $this->assertEquals('http://www.test.com/blog', $blogUrl);
        $this->assertEquals('http://www.test.com/blog/100', $blogPostUrl);
    }

    /**
     *
     */
    public function testReset()
    {
        /**
         * @var MiddlewareDispatcher $blogDispatcher
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
     * @param MiddlewareDispatcher|null $blogDispatcher
     *
     * @return MiddlewareDispatcher
     */
    protected function setupDispatcher(MiddlewareDispatcher &$blogDispatcher = null)
    {
        // site dispatcher

        $dispatcher = $this->dispatcherFactory->createDispatcher();

        // blog dispatcher

        $blogDispatcher = $this->dispatcherFactory->createDispatcher();

        // blog dispatcher default route params

        $blogDispatcher->getDefaultRoute()->setHost('www.test.com')->setSecure(false);

        // blog posts lists middleware (path based routing)

        $blogDispatcher->addGetRoute('/blog',
            function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
                // stop middleware chain execution
                return $response->withBody($this->streamFor('<h1>Posts list</h1><p>Post1</p><p>Post2</p>'));
            }
        )->setName('blog.list');

        // blog post middleware (path based routing)

        $blogDispatcher->addGetRoute('/blog/{id}',
            function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
                $this->assertArrayHasKey('1.id', $request->getAttributes());
                $id = (int)$request->getAttribute('1.id'); // prefix for route-one attributes
                // post id is valid
                if ($id === 1) {
                    // stop middleware chain execution
                    return $response->withBody($this->streamFor(sprintf('<h1>Post #%s</h1><p>Example post</p>', $id)));
                }
                // post not found, continue to the next middleware
                return $next($request, $response);
            }
        )->setName('blog.post');

        // blog page not found middleware (no routing, executes for each request)

        $blogDispatcher->addMiddleware(
            function (ServerRequestInterface $request, ResponseInterface $response, callable $next)
            {
                // 404 response
                return $response
                    ->withStatus(404)
                    ->withBody($this->streamFor('<h1>Page not found</h1>'));
            }
        );

        // blog middleware

        $dispatcher->addMiddleware(
            function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use ($blogDispatcher) {
                return $next($request, $blogDispatcher->dispatch($request, $response));
            }
        );

        // page layout middleware

        $dispatcher->addMiddleware(
            function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
                $content = $response->getBody()->getContents();
                return $response
                    ->withBody($this->streamFor('<html><body>' . $content . '</body></html>'))
                    ->withHeader('content-type', 'text/html; charset=utf-8');
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
