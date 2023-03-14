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
 * @internal
 */
class JsonApiMiddlewareTest extends TestCase
{
    public function testIncorrectHeaders(): void
    {
        $headersChecker = $this->getMockBuilder(HeadersChecker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headersChecker->expects(static::once())
            ->method('checkHeaders')
            ->will(static::throwException(new JsonApiException([])));
        // @var HeadersChecker $headersChecker

        $factory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factory->expects(static::once())
            ->method('createHeadersChecker')
            ->willReturn($headersChecker);
        // @var Factory $factory

        $manager = $this->getMockBuilder(Manager::class)
            ->setConstructorArgs([new Configuration(), $factory])
            ->getMock();
        $manager->expects(static::once())
            ->method('getFactory')
            ->willReturn($factory);
        // @var Manager $manager

        // @var RequestHandlerInterface $handler
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)
            ->getMock();

        $response = (new JsonApiMiddleware(new ResponseFactory(), $manager))->process(new ServerRequest(), $handler);

        static::assertEquals(400, $response->getStatusCode());
    }

    public function testCorrectHeaders(): void
    {
        $headersChecker = $this->getMockBuilder(HeadersChecker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headersChecker->expects(static::once())
            ->method('checkHeaders');
        // @var HeadersChecker $headersChecker

        $factory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factory->expects(static::once())
            ->method('createHeadersChecker')
            ->willReturn($headersChecker);
        $factory->expects(static::once())
            ->method('createQueryParametersParser')
            ->willReturn(new QueryParametersParser());
        // @var Factory $factory

        $manager = $this->getMockBuilder(Manager::class)
            ->setConstructorArgs([new Configuration(), $factory])
            ->getMock();
        $manager->expects(static::once())
            ->method('getFactory')
            ->willReturn($factory);
        $manager->expects(static::once())
            ->method('setRequestQueryParameters')
            ->willReturnArgument(0);
        // @var Manager $manager

        $handler = $this->getMockBuilder(RequestHandlerInterface::class)
            ->getMock();
        $handler->expects(static::once())
            ->method('handle')
            ->willReturn(new Response());
        // @var RequestHandlerInterface $handler

        $response = (new JsonApiMiddleware(new ResponseFactory(), $manager))->process(new ServerRequest(), $handler);

        static::assertEquals(200, $response->getStatusCode());
    }
}
