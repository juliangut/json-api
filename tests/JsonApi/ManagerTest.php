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

namespace Jgut\JsonApi\Tests;

use Jgut\JsonApi\Configuration;
use Jgut\JsonApi\Encoding\Encoder;
use Jgut\JsonApi\Encoding\Factory;
use Jgut\JsonApi\Encoding\Http\QueryParametersParser;
use Jgut\JsonApi\Manager;
use Laminas\Diactoros\ServerRequest;
use Neomerx\JsonApi\Schema\Error;
use Neomerx\JsonApi\Schema\SchemaContainer;
use PHPUnit\Framework\TestCase;

/**
 * JSON-API manager tests.
 */
class ManagerTest extends TestCase
{
    public function testRequestManipulation(): void
    {
        $request = new ServerRequest();
        $queryParameters = new QueryParametersParser();
        $factory = new Factory();

        $manager = new Manager(new Configuration(), $factory);

        self::assertSame($factory, $manager->getFactory());

        $request = $manager->setRequestQueryParameters($request, $queryParameters);

        self::assertSame($queryParameters, $manager->getRequestQueryParameters($request));
    }

    public function testEncodeErrors(): void
    {
        $encoder = $this->getMockBuilder(Encoder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $encoder->expects(self::once())
            ->method('encodeErrors')
            ->will(self::returnValue('ENCODED'));
        /* @var Encoder $encoder */

        $container = $this->getMockBuilder(SchemaContainer::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var SchemaContainer $container */

        $factory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factory->expects(self::once())
            ->method('createEncoder')
            ->will(self::returnValue($encoder));
        $factory->expects(self::once())
            ->method('createSchemaContainer')
            ->will(self::returnValue($container));
        /* @var Factory $factory */

        self::assertEquals('ENCODED', (new Manager(new Configuration(), $factory))->encodeErrors(new Error()));
    }

    public function testEncodeResources(): void
    {
        $request = new ServerRequest();
        $queryParameters = new QueryParametersParser();

        $encoder = $this->getMockBuilder(Encoder::class)
            ->disableOriginalConstructor()
            ->getMock();
//        $encoder->expects(self::once())
//            ->method('withMeta');
//        $encoder->expects(self::once())
//            ->method('withLinks');
        $encoder->expects(self::once())
            ->method('encodeData')
            ->will(self::returnValue('ENCODED'));
        /* @var Encoder $encoder */

        $container = $this->getMockBuilder(SchemaContainer::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var SchemaContainer $container */

        $factory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factory->expects(self::once())
            ->method('createEncoder')
            ->will(self::returnValue($encoder));
        $factory->expects(self::once())
            ->method('createSchemaContainer')
            ->will(self::returnCallback(function (array $metadata) use ($container) {
                self::assertCount(1, $metadata);

                return $container;
            }));
        /* @var Factory $factory */

        $configuration = $this->getMockBuilder(Configuration::class)
            ->setMethods(['getMetadata', 'getLinks', 'getSources'])
            ->getMock();
        $configuration->expects(self::once())
            ->method('getSources')
            ->will(self::returnValue([__DIR__ . '/Files/Annotation/Valid']));
        /* @var Configuration $configuration */

        $manager = new Manager($configuration, $factory);

        $encoded = $manager->encodeResources(
            new \stdClass(),
            $manager->setRequestQueryParameters($request, $queryParameters),
            ['resourceB'],
            null
        );

        self::assertEquals('ENCODED', $encoded);
    }
}
