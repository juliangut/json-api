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
use Jgut\JsonApi\Mapping\Driver\DriverFactory;
use Jgut\Mapping\Driver\DriverFactoryInterface;
use Jgut\Mapping\Metadata\MetadataResolver;
use Laminas\Diactoros\ServerRequest;
use Neomerx\JsonApi\Schema\Error;
use Neomerx\JsonApi\Schema\ErrorCollection;
use Neomerx\JsonApi\Schema\SchemaContainer;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ManagerTest extends TestCase
{
    public function testRequestManipulation(): void
    {
        $request = new ServerRequest();
        $queryParameters = new QueryParametersParser();
        $factory = new Factory();

        $manager = new Manager(new Configuration(), $factory);

        static::assertSame($factory, $manager->getFactory());

        $request = $manager->setRequestQueryParameters($request, $queryParameters);

        static::assertSame($queryParameters, $manager->getRequestQueryParameters($request));
    }

    public function testEncodeErrors(): void
    {
        $encoder = $this->getMockBuilder(Encoder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $encoder->expects(static::once())
            ->method('encodeErrors')
            ->willReturn('ENCODED');

        $container = $this->getMockBuilder(SchemaContainer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factory->expects(static::once())
            ->method('createEncoder')
            ->willReturn($encoder);
        $factory->expects(static::once())
            ->method('createSchemaContainer')
            ->willReturn($container);

        $errors = (new ErrorCollection())->add(new Error());
        static::assertEquals('ENCODED', (new Manager(new Configuration(), $factory))->encodeErrors($errors));
    }

    public function testEncodeResources(): void
    {
        $request = new ServerRequest();
        $queryParameters = new QueryParametersParser();

        $encoder = $this->getMockBuilder(Encoder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $encoder->expects(static::once())
            ->method('encodeData')
            ->willReturn('ENCODED');

        $container = $this->getMockBuilder(SchemaContainer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factory->expects(static::once())
            ->method('createEncoder')
            ->willReturn($encoder);
        $factory->expects(static::once())
            ->method('createSchemaContainer')
            ->willReturnCallback(static function (array $metadata) use ($container) {
                self::assertCount(1, $metadata);

                return $container;
            });

        $sources = \PHP_VERSION_ID < 80_000
            ? [
                [
                    'type' => DriverFactoryInterface::DRIVER_ANNOTATION,
                    'path' => __DIR__ . '/Mapping/Files/Classes/Valid/Annotation',
                ],
            ]
            : [__DIR__ . '/Mapping/Files/Classes/Valid/Attribute'];

        $configuration = $this->getMockBuilder(Configuration::class)
            ->setMethods(['getMetadataResolver', 'getMetadata', 'getLinks', 'getSources'])
            ->getMock();
        $configuration->expects(static::once())
            ->method('getMetadataResolver')
            ->willReturn(new MetadataResolver(new DriverFactory()));
        $configuration->expects(static::once())
            ->method('getSources')
            ->willReturn($sources);

        $manager = new Manager($configuration, $factory);

        $encoded = $manager->encodeResources(
            new stdClass(),
            $manager->setRequestQueryParameters($request, $queryParameters),
            ['resourceTwo'],
        );

        static::assertEquals('ENCODED', $encoded);
    }
}
