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

interface FactoryInterface extends BaseFactoryInterface
{
    public function createEncoder(SchemaContainerInterface $container): EncoderInterface;

    public function createQueryParametersParser(ServerRequestInterface $request): QueryParametersParserInterface;

    public function createHeadersChecker(): HeadersCheckerInterface;
}
