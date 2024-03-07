<?php

/*
 * (c) 2018-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Tests;

use InvalidArgumentException;
use Jgut\JsonApi\Configuration;
use Jgut\JsonApi\Encoding\Options;
use Jgut\JsonApi\Mapping\Driver\DriverFactory;
use Jgut\JsonApi\Schema\MetadataSchema;
use Jgut\JsonApi\Schema\Resolver;
use Jgut\Mapping\Metadata\MetadataResolver;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ConfigurationTest extends TestCase
{
    public function testDefaults(): void
    {
        $configuration = new Configuration();

        static::assertEquals(Configuration::QUERY_PARAMETERS_REQUEST_KEY, $configuration->getAttributeName());
        static::assertEmpty($configuration->getSources());
        static::assertNull($configuration->getEncodingOptions());
        static::assertNull($configuration->getUrlPrefix());
        static::assertEquals(MetadataSchema::class, $configuration->getSchemaClass());
    }

    public function testUnknownParameter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The following configuration parameters are not recognized: unknown.');

        new Configuration(['unknown' => 'unknown']);
    }

    public function testBadSource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches(
            '/Mapping source must be a string, array or .+\DriverInterface, integer given\./',
        );

        new Configuration(['sources' => [10]]);
    }

    public function testConfiguration(): void
    {
        $paths = [
            '/path/to/directory',
            '/path/to/file.php',
        ];
        $metadataResolver = new MetadataResolver(new DriverFactory());
        $schemaResolver = $this->getMockBuilder(Resolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $encodingOptions = $this->getMockBuilder(Options::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configuration = new Configuration([
            'attributeName' => 'Name',
            'sources' => $paths,
            'metadataResolver' => $metadataResolver,
            'schemaClass' => 'Class',
            'schemaResolver' => $schemaResolver,
            'encodingOptions' => $encodingOptions,
            'urlPrefix' => 'http://example.com',
            'jsonApiVersion' => '1.1',
            'jsonApiMeta' => ['meta' => 'value'],
        ]);

        static::assertEquals('Name', $configuration->getAttributeName());
        static::assertEquals($paths, $configuration->getSources());
        static::assertEquals($metadataResolver, $configuration->getMetadataResolver());
        static::assertEquals('Class', $configuration->getSchemaClass());
        static::assertEquals($schemaResolver, $configuration->getSchemaResolver());
        static::assertEquals($encodingOptions, $configuration->getEncodingOptions());
        static::assertEquals('http://example.com', $configuration->getUrlPrefix());
        static::assertEquals('1.1', $configuration->getJsonApiVersion());
        static::assertEquals(['meta' => 'value'], $configuration->getJsonApiMeta());
    }
}
