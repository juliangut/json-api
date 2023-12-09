<?php

/*
 * (c) 2018-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Tests\Encoding;

use Jgut\JsonApi\Encoding\Encoder;
use Jgut\JsonApi\Encoding\Factory;
use Jgut\JsonApi\Encoding\FieldSetFilter;
use Jgut\JsonApi\Encoding\Http\HeadersChecker;
use Jgut\JsonApi\Encoding\Http\QueryParametersParser;
use Laminas\Diactoros\ServerRequest;
use Neomerx\JsonApi\Contracts\Schema\SchemaContainerInterface;
use Neomerx\JsonApi\Schema\SchemaContainer;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class FactoryTest extends TestCase
{
    public function testCreateEncoder(): void
    {
        /** @var SchemaContainerInterface $container */
        $container = $this->getMockBuilder(SchemaContainer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory = new Factory();

        static::assertInstanceOf(Encoder::class, $factory->createEncoder($container));
    }

    public function testCreateFieldSetFilter(): void
    {
        $factory = new Factory();

        static::assertInstanceOf(FieldSetFilter::class, $factory->createFieldSetFilter([]));
    }

    public function testQueryParametersParser(): void
    {
        $parameters = [
            'fields' => ['resource' => 'a,b'],
            'sort' => '-a,b',
        ];

        $request = (new ServerRequest())->withQueryParams($parameters);

        $factory = new Factory();

        $queryParser = $factory->createQueryParametersParser($request);

        static::assertInstanceOf(QueryParametersParser::class, $queryParser);
        static::assertEquals(['resource' => ['a', 'b']], $queryParser->getFields());
        static::assertEquals(['a' => false, 'b' => true], $queryParser->getSorts());
    }

    public function testCreateHeadersChecker(): void
    {
        $factory = new Factory();

        static::assertInstanceOf(HeadersChecker::class, $factory->createHeadersChecker());
    }
}
