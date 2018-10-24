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

namespace Jgut\JsonApi\Encoding;

use Jgut\JsonApi\Encoding\Http\HeadersChecker;
use Jgut\JsonApi\Encoding\Http\HeadersCheckerInterface;
use Jgut\JsonApi\Encoding\Http\QueryParametersParser;
use Jgut\JsonApi\Encoding\Http\QueryParametersParserInterface;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use Neomerx\JsonApi\Encoder\EncoderOptions;
use Neomerx\JsonApi\Factories\Factory as BaseFactory;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Custom factory.
 */
class Factory extends BaseFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createEncoder(
        ContainerInterface $container,
        EncoderOptions $encoderOptions = null
    ): EncoderInterface {
        $encoder = new Encoder($this, $container, $encoderOptions);
        $encoder->setLogger($this->logger);

        return $encoder;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Neomerx\JsonApi\Exceptions\JsonApiException
     */
    public function createQueryParametersParser(ServerRequestInterface $request): QueryParametersParserInterface
    {
        return new QueryParametersParser($request->getQueryParams());
    }

    /**
     * {@inheritdoc}
     */
    public function createHeadersChecker(): HeadersCheckerInterface
    {
        return new HeadersChecker($this->createHeaderParametersParser());
    }
}
