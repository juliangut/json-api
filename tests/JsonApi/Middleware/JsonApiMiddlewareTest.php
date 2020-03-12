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

namespace Jgut\JsonApi\Tests\Middleware;

use Jgut\JsonApi\Configuration;
use Jgut\JsonApi\Encoding\Factory;
use Jgut\JsonApi\Encoding\Http\HeadersChecker;
use Jgut\JsonApi\Encoding\Http\QueryParametersParser;
use Jgut\JsonApi\Manager;
use Jgut\JsonApi\Middleware\JsonApiMiddleware;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequest;
use Neomerx\JsonApi\Exceptions\JsonApiException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * JSON API request handler middleware tests.
 */
class JsonApiMiddlewareTest extends TestCase
{
    public function testIncorrectHeaders(): void
    {
        $headersChecker = $this->getMockBuilder(HeadersChecker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headersChecker->expects(self::once())
            ->method('checkHeaders')
            ->will($this->throwException(new JsonApiException([])));
        /* @var HeadersChecker $headersChecker */

        $factory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factory->expects(self::once())
            ->method('createHeadersChecker')
            ->will(self::returnValue($headersChecker));
        /* @var Factory $factory */

        $manager = $this->getMockBuilder(Manager::class)
            ->setConstructorArgs([new Configuration(), $factory])
            ->getMock();
        $manager->expects(self::once())
            ->method('getFactory')
            ->will(self::returnValue($factory));
        /* @var Manager $manager */

        /* @var RequestHandlerInterface $handler */
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)
            ->getMock();

        $response = (new JsonApiMiddleware(new ResponseFactory(), $manager))->process(new ServerRequest(), $handler);

        self::assertEquals(400, $response->getStatusCode());
    }

    public function testCorrectHeaders(): void
    {
        $headersChecker = $this->getMockBuilder(HeadersChecker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headersChecker->expects(self::once())
            ->method('checkHeaders');
        /* @var HeadersChecker $headersChecker */

        $factory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factory->expects(self::once())
            ->method('createHeadersChecker')
            ->will(self::returnValue($headersChecker));
        $factory->expects(self::once())
            ->method('createQueryParametersParser')
            ->will(self::returnValue(new QueryParametersParser()));
        /* @var Factory $factory */

        $manager = $this->getMockBuilder(Manager::class)
            ->setConstructorArgs([new Configuration(), $factory])
            ->getMock();
        $manager->expects(self::once())
            ->method('getFactory')
            ->will(self::returnValue($factory));
        $manager->expects(self::once())
            ->method('setRequestQueryParameters')
            ->will(self::returnArgument(0));
        /* @var Manager $manager */

        $handler = $this->getMockBuilder(RequestHandlerInterface::class)
            ->getMock();
        $handler->expects(self::once())
            ->method('handle')
            ->will(self::returnValue(new Response()));
        /* @var RequestHandlerInterface $handler */

        $response = (new JsonApiMiddleware(new ResponseFactory(), $manager))->process(new ServerRequest(), $handler);

        self::assertEquals(200, $response->getStatusCode());
    }
}
