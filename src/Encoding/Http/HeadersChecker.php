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
use Neomerx\JsonApi\Exceptions\InvalidArgumentException;
use Neomerx\JsonApi\Exceptions\JsonApiException;
use Neomerx\JsonApi\Schema\Error;
use Psr\Http\Message\ServerRequestInterface;

class HeadersChecker implements HeadersCheckerInterface
{
    private HeaderParametersParserInterface $headerParser;

    public function __construct(HeaderParametersParserInterface $headerParser)
    {
        $this->headerParser = $headerParser;
    }

    public function checkHeaders(ServerRequestInterface $request): void
    {
        $this->checkContentTypeHeader($request);
        $this->checkAcceptHeader($request);
    }

    /**
     * Check Content-Type header validity.
     *
     * @throws JsonApiException
     */
    protected function checkContentTypeHeader(ServerRequestInterface $request): void
    {
        if (\count($request->getHeader('Content-Type')) === 1) {
            try {
                /** @var MediaTypeInterface $contentType */
                $contentType = $this->headerParser
                    ->parseContentTypeHeader($request->getHeaderLine('Content-Type'));
            } catch (InvalidArgumentException $exception) {
                throw new JsonApiException(
                    new Error(
                        null,
                        null,
                        null,
                        (string) JsonApiException::HTTP_CODE_BAD_REQUEST,
                        null,
                        'Content-Type header parse error',
                        $exception->getMessage(),
                    ),
                    JsonApiException::HTTP_CODE_BAD_REQUEST,
                    $exception,
                );
            }

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
                null,
                (string) JsonApiException::HTTP_CODE_UNSUPPORTED_MEDIA_TYPE,
                null,
                'Unsupported content type',
                'Content-Type header should be ' . MediaTypeInterface::JSON_API_MEDIA_TYPE,
            ),
            JsonApiException::HTTP_CODE_UNSUPPORTED_MEDIA_TYPE,
        );
    }

    /**
     * Check Accept header validity.
     *
     * @throws JsonApiException
     */
    protected function checkAcceptHeader(ServerRequestInterface $request): void
    {
        try {
            /** @var MediaTypeInterface $mediaType */
            foreach ($this->headerParser->parseAcceptHeader($request->getHeaderLine('Accept')) as $mediaType) {
                if ($mediaType->getType() === MediaTypeInterface::JSON_API_TYPE
                    && $mediaType->getSubType() === MediaTypeInterface::JSON_API_SUB_TYPE
                ) {
                    return;
                }
            }
        } catch (InvalidArgumentException $exception) {
            throw new JsonApiException(
                new Error(
                    null,
                    null,
                    null,
                    (string) JsonApiException::HTTP_CODE_BAD_REQUEST,
                    null,
                    'Accept header parse error',
                    $exception->getMessage(),
                ),
                JsonApiException::HTTP_CODE_BAD_REQUEST,
                $exception,
            );
        }

        throw new JsonApiException(
            new Error(
                null,
                null,
                null,
                (string) JsonApiException::HTTP_CODE_NOT_ACCEPTABLE,
                null,
                'Unsupported media type',
                'Accept header value is not valid',
            ),
            JsonApiException::HTTP_CODE_NOT_ACCEPTABLE,
        );
    }
}
