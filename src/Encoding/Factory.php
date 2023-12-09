<?php

/*
 * (c) 2018-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Encoding;

use Jgut\JsonApi\Encoding\Http\HeadersChecker;
use Jgut\JsonApi\Encoding\Http\HeadersCheckerInterface;
use Jgut\JsonApi\Encoding\Http\QueryParametersParser;
use Jgut\JsonApi\Encoding\Http\QueryParametersParserInterface;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Representation\FieldSetFilterInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaContainerInterface;
use Neomerx\JsonApi\Factories\Factory as BaseFactory;
use Neomerx\JsonApi\Http\Headers\HeaderParametersParser;
use Psr\Http\Message\ServerRequestInterface;

class Factory extends BaseFactory implements FactoryInterface
{
    public function createEncoder(SchemaContainerInterface $container): EncoderInterface
    {
        return new Encoder($this, $container);
    }

    /**
     * @param array<mixed> $fieldSets
     */
    public function createFieldSetFilter(array $fieldSets): FieldSetFilterInterface
    {
        return new FieldSetFilter($fieldSets);
    }

    public function createQueryParametersParser(ServerRequestInterface $request): QueryParametersParserInterface
    {
        return new QueryParametersParser($request->getQueryParams());
    }

    public function createHeadersChecker(): HeadersCheckerInterface
    {
        return new HeadersChecker(new HeaderParametersParser($this));
    }
}
