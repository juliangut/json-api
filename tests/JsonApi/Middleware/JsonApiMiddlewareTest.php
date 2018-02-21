<?php

/*
 * json-api (https://github.com/juliangut/json-api).
 * PSR-7 aware json-api integration.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Tests;

use Jgut\JsonApi\Encoding\Factory;
use Jgut\JsonApi\Encoding\Http\HeadersChecker;
use Jgut\JsonApi\Encoding\Http\QueryParametersParser;
use Jgut\JsonApi\Manager;
use Jgut\JsonApi\Middleware\JsonApiMiddleware;
use Neomerx\JsonApi\Exceptions\JsonApiException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

/**
 * JSON API request handler middleware tests.
 */
class JsonApiMiddlewareTest extends TestCase
{
    public function testPSR15NoResponse()
    {
        $headersChecker = $this->getMockBuilder(HeadersChecker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headersChecker->expects(self::any())
            ->method('checkHeaders')
            ->will($this->throwException(new JsonApiException([])));
        /* @var HeadersChecker $headersChecker */

        $factory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factory->expects(self::any())
            ->method('createHeadersChecker')
            ->will($this->returnValue($headersChecker));
        /* @var Factory $factory */

        $manager = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::once())
            ->method('getFactory')
            ->will($this->returnValue($factory));
        /* @var Manager $manager */

        $handler = $this->getMockBuilder(RequestHandlerInterface::class)
            ->getMock();
        /* @var RequestHandlerInterface $handler */

        $response = (new JsonApiMiddleware($manager))->process(new ServerRequest(), $handler);

        self::assertEquals(400, $response->getStatusCode());
    }

    public function testPSR15Response()
    {
        $headersChecker = $this->getMockBuilder(HeadersChecker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headersChecker->expects(self::any())
            ->method('checkHeaders');
        /* @var HeadersChecker $headersChecker */

        $factory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factory->expects(self::any())
            ->method('createHeadersChecker')
            ->will($this->returnValue($headersChecker));
        $factory->expects(self::any())
            ->method('createQueryParametersParser')
            ->will($this->returnValue(new QueryParametersParser()));
        /* @var Factory $factory */

        $manager = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::once())
            ->method('getFactory')
            ->will($this->returnValue($factory));
        $manager->expects(self::once())
            ->method('setRequestQueryParameters')
            ->will($this->returnArgument(0));
        /* @var Manager $manager */

        $handler = $this->getMockBuilder(RequestHandlerInterface::class)
            ->getMock();
        $handler->expects(self::once())
            ->method('handle')
            ->will($this->returnValue(new Response()));
        /* @var RequestHandlerInterface $handler */

        $response = (new JsonApiMiddleware($manager))->process(new ServerRequest(), $handler);

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('application/vnd.api+json; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }

    public function testCallableNoResponse()
    {
        $headersChecker = $this->getMockBuilder(HeadersChecker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headersChecker->expects(self::any())
            ->method('checkHeaders')
            ->will($this->throwException(new JsonApiException([])));
        /* @var HeadersChecker $headersChecker */

        $factory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factory->expects(self::any())
            ->method('createHeadersChecker')
            ->will($this->returnValue($headersChecker));
        /* @var Factory $factory */

        $manager = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::once())
            ->method('getFactory')
            ->will($this->returnValue($factory));
        /* @var Manager $manager */

        $callable = function ($request, $response) {
            return $response;
        };

        /** @var Response $response */
        $response = (new JsonApiMiddleware($manager))(new ServerRequest(), new Response(), $callable);

        self::assertEquals(400, $response->getStatusCode());
    }

    public function testCallableResponse()
    {
        $headersChecker = $this->getMockBuilder(HeadersChecker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headersChecker->expects(self::any())
            ->method('checkHeaders');
        /* @var HeadersChecker $headersChecker */

        $factory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factory->expects(self::any())
            ->method('createHeadersChecker')
            ->will($this->returnValue($headersChecker));
        $factory->expects(self::any())
            ->method('createQueryParametersParser')
            ->will($this->returnValue(new QueryParametersParser()));
        /* @var Factory $factory */

        $manager = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::once())
            ->method('getFactory')
            ->will($this->returnValue($factory));
        $manager->expects(self::once())
            ->method('setRequestQueryParameters')
            ->will($this->returnArgument(0));
        /* @var Manager $manager */

        $callable = function ($request, $response) {
            return $response;
        };

        /** @var Response $response */
        $response = (new JsonApiMiddleware($manager))(new ServerRequest(), new Response(), $callable);

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('application/vnd.api+json; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }
}
