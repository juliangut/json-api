<?php

/*
 * (c) 2018-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Tests\Encoding\Http;

use Jgut\JsonApi\Encoding\Http\QueryParametersParser;
use Neomerx\JsonApi\Exceptions\JsonApiException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class QueryParametersParserTest extends TestCase
{
    public function testInvalidFieldsType(): void
    {
        $this->expectException(JsonApiException::class);
        $this->expectExceptionMessage('JSON API error');

        $parameters = [
            'fields' => 'should_be_array',
        ];

        new QueryParametersParser($parameters);
    }

    public function testInvalidFieldsKey(): void
    {
        $this->expectException(JsonApiException::class);
        $this->expectExceptionMessage('JSON API error');

        $parameters = [
            'fields' => ['key_should_be_string'],
        ];

        new QueryParametersParser($parameters);
    }

    public function testFieldsParsing(): void
    {
        $parameters = [
            'fields' => ['resource' => 'a,b'],
        ];

        $queryParser = new QueryParametersParser($parameters);

        static::assertEquals(['resource' => ['a', 'b']], $queryParser->getFields());

        $queryParser->setFields(['another' => ['c', 'd']]);
        static::assertEquals(['another' => ['c', 'd']], $queryParser->getFields());
    }

    public function testEmptyIncludes(): void
    {
        $this->expectException(JsonApiException::class);
        $this->expectExceptionMessage('JSON API error');

        $parameters = [
            'include' => ',,',
        ];

        new QueryParametersParser($parameters);
    }

    public function testIncludesParsing(): void
    {
        $parameters = [
            'include' => 'a,b',
        ];

        $queryParser = new QueryParametersParser($parameters);

        static::assertEquals(['a', 'b'], $queryParser->getIncludes());

        $queryParser->setIncludes(['c', 'd']);
        static::assertEquals(['c', 'd'], $queryParser->getIncludes());
    }

    public function testSortParsing(): void
    {
        $parameters = [
            'sort' => '-a,b',
        ];

        $queryParser = new QueryParametersParser($parameters);

        static::assertEquals(['a' => false, 'b' => true], $queryParser->getSorts());

        $queryParser->setSorts(['c' => true, 'd' => false]);
        static::assertEquals(['c' => true, 'd' => false], $queryParser->getSorts());
    }

    public function testInvalidPage(): void
    {
        $this->expectException(JsonApiException::class);
        $this->expectExceptionMessage('JSON API error');

        $parameters = [
            'page' => ['page' => 1.0],
        ];

        new QueryParametersParser($parameters);
    }

    public function testPagingParsing(): void
    {
        $parameters = [
            'page' => ['offset' => 10, 'count' => 10],
        ];

        $queryParser = new QueryParametersParser($parameters);

        static::assertEquals(['offset' => 10, 'count' => 10], $queryParser->getPaging());

        $queryParser->setPaging(['page' => 5, 'size' => 10]);
        static::assertEquals(['page' => 5, 'size' => 10], $queryParser->getPaging());
    }

    public function testFiltersParsing(): void
    {
        $parameters = [
            'filter' => 'anything',
        ];

        $queryParser = new QueryParametersParser($parameters);

        static::assertEquals('anything', $queryParser->getFilters());

        $queryParser->setFilters(['something' => 'anything']);
        static::assertEquals(['something' => 'anything'], $queryParser->getFilters());
    }
}
