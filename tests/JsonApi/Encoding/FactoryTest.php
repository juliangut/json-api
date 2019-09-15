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

use Jgut\JsonApi\Encoding\Encoder;
use Jgut\JsonApi\Encoding\Factory;
use Jgut\JsonApi\Encoding\FieldSetFilter;
use Jgut\JsonApi\Encoding\Http\HeadersChecker;
use Jgut\JsonApi\Encoding\Http\QueryParametersParser;
use Neomerx\JsonApi\Contracts\Schema\SchemaContainerInterface;
use Neomerx\JsonApi\Schema\SchemaContainer;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;

/**
 * Custom factory tests.
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

        self::assertInstanceOf(Encoder::class, $factory->createEncoder($container));
    }

    public function testCreateFieldSetFilter(): void
    {
        $factory = new Factory();

        self::assertInstanceOf(FieldSetFilter::class, $factory->createFieldSetFilter([]));
    }

    public function testQueryParametersParser(): void
    {
        $parameters = [
            'fields' => ['resource' => 'a,b'],
            'sort' => '-a,b',
        ];

        $request = (new ServerRequest())->withQueryParams($parameters);

        $factory = new Factory();

        $queryParametersParser = $factory->createQueryParametersParser($request);

        self::assertInstanceOf(QueryParametersParser::class, $queryParametersParser);
        self::assertEquals(['resource' => ['a', 'b']], $queryParametersParser->getFields());
        self::assertEquals(['a' => false, 'b' => true], $queryParametersParser->getSorts());
    }

    public function testCreateHeadersChecker(): void
    {
        $factory = new Factory();

        self::assertInstanceOf(HeadersChecker::class, $factory->createHeadersChecker());
    }
}
