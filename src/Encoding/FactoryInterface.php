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

use Jgut\JsonApi\Encoding\Http\HeadersCheckerInterface;
use Jgut\JsonApi\Encoding\Http\QueryParametersParserInterface;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface as BaseFactoryInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Custom factory interface.
 */
interface FactoryInterface extends BaseFactoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @return Encoder
     */
    public function createEncoder(SchemaContainerInterface $container): EncoderInterface;

    /**
     * Create request query parameters parser.
     *
     * @param ServerRequestInterface $request
     *
     * @return QueryParametersParserInterface
     */
    public function createQueryParametersParser(ServerRequestInterface $request): QueryParametersParserInterface;

    /**
     * Create headers checker.
     *
     * @return HeadersCheckerInterface
     */
    public function createHeadersChecker(): HeadersCheckerInterface;
}
