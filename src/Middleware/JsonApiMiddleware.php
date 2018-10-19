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

namespace Jgut\JsonApi\Middleware;

use Jgut\JsonApi\Manager;
use Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use Neomerx\JsonApi\Exceptions\JsonApiException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;

/**
 * JSON API request handler middleware.
 */
class JsonApiMiddleware implements MiddlewareInterface
{
    /**
     * JSON API manager.
     *
     * @var Manager
     */
    protected $manager;

    /**
     * JsonApiMiddleware constructor.
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $request = $this->getRequestWithQueryParameters($request);
        } catch (JsonApiException $exception) {
            return $this->getResponseFromException($exception);
        }

        $response = $handler->handle($request);

        return $response->withHeader('Content-Type', MediaTypeInterface::JSON_API_MEDIA_TYPE . '; charset=utf-8');
    }

    /**
     * Get request bundled with query parameters parser.
     *
     * @param ServerRequestInterface $request
     *
     * @throws JsonApiException
     *
     * @return ServerRequestInterface
     */
    protected function getRequestWithQueryParameters(ServerRequestInterface $request): ServerRequestInterface
    {
        $factory = $this->manager->getFactory();

        $factory->createHeadersChecker()->checkHeaders($request);

        $queryParameters = $factory->createQueryParametersParser($request);

        return $this->manager->setRequestQueryParameters($request, $queryParameters);
    }

    /**
     * Get new response object from exception.
     *
     * @param JsonApiException $exception
     *
     * @return ResponseInterface
     */
    protected function getResponseFromException(JsonApiException $exception): ResponseInterface
    {
        $response = new Response('php://temp', $exception->getHttpCode());
        $response->getBody()->write($this->manager->encodeErrors($exception->getErrors()));

        return $response;
    }
}
