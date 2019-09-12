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
use Jgut\JsonApi\Mapping\Driver\DriverFactory;
use Jgut\JsonApi\Schema\MetadataSchema;
use Jgut\JsonApi\Schema\Resolver;
use Jgut\Mapping\Metadata\MetadataResolver;
use Neomerx\JsonApi\Encoder\EncoderOptions;
use PHPUnit\Framework\TestCase;

/**
 * Configuration tests.
 */
class ConfigurationTest extends TestCase
{
    public function testDefaults()
    {
        $configuration = new Configuration();

        self::assertEquals(Configuration::QUERY_PARAMETERS_REQUEST_KEY, $configuration->getAttributeName());
        self::assertEmpty($configuration->getSources());
        self::assertInstanceOf(MetadataResolver::class, $configuration->getMetadataResolver());
        self::assertInstanceOf(Resolver::class, $configuration->getSchemaResolver());
        self::assertInstanceOf(EncoderOptions::class, $configuration->getEncoderOptions());
        self::assertNull($configuration->getUrlPrefix());
        self::assertEquals(MetadataSchema::class, $configuration->getSchemaClass());
    }

    public function testUnknownParameter()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The following configuration parameters are not recognized: unknown');

        new Configuration(['unknown' => 'unknown']);
    }

    public function testAttributeName()
    {
        $configuration = new Configuration(['attributeName' => 'Name']);

        self::assertEquals('Name', $configuration->getAttributeName());
    }

    public function testBadSource()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp(
            '/Mapping source must be a string, array or .+\DriverInterface, integer given/'
        );

        new Configuration(['sources' => [10]]);
    }

    public function testSourcePaths()
    {
        $paths = [
            '/path/to/directory',
            '/path/to/file.php',
        ];

        $configuration = new Configuration(['sources' => $paths]);

        self::assertEquals($paths, $configuration->getSources());
    }

    public function testMetadataResolver()
    {
        $metadataResolver = new MetadataResolver(new DriverFactory());

        $configuration = new Configuration(['metadataResolver' => $metadataResolver]);

        self::assertEquals($metadataResolver, $configuration->getMetadataResolver());
    }

    public function testSchemaResolver()
    {
        $schemaResolver = $this->getMockBuilder(Resolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configuration = new Configuration(['schemaResolver' => $schemaResolver]);

        self::assertEquals($schemaResolver, $configuration->getSchemaResolver());
    }

    public function testEncoderOptions()
    {
        $encoderOptions = $this->getMockBuilder(EncoderOptions::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configuration = new Configuration(['encoderOptions' => $encoderOptions]);

        self::assertEquals($encoderOptions, $configuration->getEncoderOptions());
    }

    public function testUrlPrefix()
    {
        $configuration = new Configuration(['urlPrefix' => 'http://example.com']);

        self::assertEquals('http://example.com', $configuration->getUrlPrefix());
    }

    public function testSchemaClass()
    {
        $configuration = new Configuration(['schemaClass' => 'Class']);

        self::assertEquals('Class', $configuration->getSchemaClass());
    }
}
