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

namespace Jgut\JsonApi\Tests\Encoding;

use Jgut\JsonApi\Encoding\Http\HeadersChecker;
use Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use Neomerx\JsonApi\Http\Headers\HeaderParametersParser;
use Neomerx\JsonApi\Http\Headers\MediaType;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;

/**
 * Request headers validity checker tests.
 */
class HeadersCheckerTest extends TestCase
{
    /**
     * @expectedException \Neomerx\JsonApi\Exceptions\JsonApiException
     * @expectedExceptionMessage JSON API error
     */
    public function testInvalidContentType()
    {
        $headerParser = $this->getMockBuilder(HeaderParametersParser::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headerParser->expects(static::once())
            ->method('parseContentTypeHeader')
            ->will($this->returnValue(new MediaType('not', 'valid')));
        /* @var HeaderParametersParser $headerParser */

        $request = (new ServerRequest())->withHeader('Content-Type', 'not/valid');

        (new HeadersChecker($headerParser))->checkHeaders($request);
    }

    /**
     * @expectedException \Neomerx\JsonApi\Exceptions\JsonApiException
     * @expectedExceptionMessage JSON API error
     */
    public function testInvalidAcceptHeader()
    {
        $mediaType = new MediaType(MediaTypeInterface::JSON_API_TYPE, MediaTypeInterface::JSON_API_SUB_TYPE);

        $headerParser = $this->getMockBuilder(HeaderParametersParser::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headerParser->expects(static::once())
            ->method('parseContentTypeHeader')
            ->will($this->returnValue($mediaType));
        $headerParser->expects(static::once())
            ->method('parseAcceptHeader')
            ->will($this->returnValue([new MediaType('not', 'valid')]));
        /* @var HeaderParametersParser $headerParser */

        $request = (new ServerRequest())
            ->withHeader('Content-Type', 'application/vnd.api+json')
            ->withHeader('Accept', 'not/valid');

        (new HeadersChecker($headerParser))->checkHeaders($request);
    }

    public function testValidHeaders()
    {
        $mediaType = new MediaType(MediaTypeInterface::JSON_API_TYPE, MediaTypeInterface::JSON_API_SUB_TYPE);

        $headerParser = $this->getMockBuilder(HeaderParametersParser::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headerParser->expects(static::once())
            ->method('parseContentTypeHeader')
            ->will($this->returnValue($mediaType));
        $headerParser->expects(static::once())
            ->method('parseAcceptHeader')
            ->will($this->returnValue([$mediaType]));
        /* @var HeaderParametersParser $headerParser */

        $request = (new ServerRequest())
            ->withHeader('Content-Type', 'application/vnd.api+json')
            ->withHeader('Accept', 'not/valid');

        (new HeadersChecker($headerParser))->checkHeaders($request);
    }
}
