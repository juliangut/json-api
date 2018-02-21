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

namespace Jgut\JsonApi\Encoding\Http;

use Neomerx\JsonApi\Contracts\Http\Headers\HeaderParametersParserInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Exceptions\JsonApiException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Request headers validity checker.
 */
class HeadersChecker implements HeadersCheckerInterface
{
    /**
     * @var HeaderParametersParserInterface
     */
    private $headerParser;

    /**
     * HeadersChecker constructor.
     *
     * @param HeaderParametersParserInterface $headerParser
     */
    public function __construct(HeaderParametersParserInterface $headerParser)
    {
        $this->headerParser = $headerParser;
    }

    /**
     * {@inheritdoc}
     */
    public function checkHeaders(ServerRequestInterface $request): void
    {
        $this->checkContentTypeHeader($request);
        $this->checkAcceptHeader($request);
    }

    /**
     * Check Content-Type header validity.
     *
     * @param ServerRequestInterface $request
     *
     * @throws JsonApiException
     */
    protected function checkContentTypeHeader(ServerRequestInterface $request): void
    {
        if (\count($request->getHeader('Content-Type')) === 1) {
            /** @var MediaTypeInterface $contentType */
            $contentType = $this->headerParser
                ->parseContentTypeHeader($request->getHeaderLine('Content-Type'));

            if ($contentType->getType() === MediaTypeInterface::JSON_API_TYPE
                && $contentType->getSubType() === MediaTypeInterface::JSON_API_SUB_TYPE
            ) {
                return;
            }
        }

        throw new JsonApiException(
            new Error(
                null,
                null,
                (string) JsonApiException::HTTP_CODE_UNSUPPORTED_MEDIA_TYPE,
                null,
                'Unsupported content type',
                'Content-Type should be ' . MediaTypeInterface::JSON_API_MEDIA_TYPE
            ),
            JsonApiException::HTTP_CODE_UNSUPPORTED_MEDIA_TYPE
        );
    }

    /**
     * Check Accept header validity.
     *
     * @param ServerRequestInterface $request
     *
     * @throws JsonApiException
     */
    protected function checkAcceptHeader(ServerRequestInterface $request): void
    {
        /** @var MediaTypeInterface $mediaType */
        foreach ($this->headerParser->parseAcceptHeader($request->getHeaderLine('Accept')) as $mediaType) {
            if ($mediaType->getType() === 'application' && $mediaType->getSubType() === 'vnd.api+json') {
                return;
            }
        }

        throw new JsonApiException(
            new Error(
                null,
                null,
                (string) JsonApiException::HTTP_CODE_NOT_ACCEPTABLE,
                null,
                'Unsupported media type',
                'Accept header value is not valid'
            ),
            JsonApiException::HTTP_CODE_NOT_ACCEPTABLE
        );
    }
}
