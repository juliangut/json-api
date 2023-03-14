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

namespace Jgut\JsonApi\Tests\Mapping\Driver;

use Jgut\JsonApi\Mapping\Driver\AnnotationDriver;
use Jgut\JsonApi\Mapping\Driver\DriverFactory;
use Jgut\Mapping\Driver\AbstractMappingDriver;
use Jgut\Mapping\Exception\DriverException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class DriverFactoryTest extends TestCase
{
    protected DriverFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new DriverFactory();
    }

    public function testInvalidDriver(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessageMatches('/^Metadata mapping driver should be of the type .+, "string" given\.$/');

        $this->factory->getDriver(['driver' => 'invalid']);
    }

    public function testAnnotationDriver(): void
    {
        static::assertInstanceOf(
            AnnotationDriver::class,
            $this->factory->getDriver(['type' => DriverFactory::DRIVER_ANNOTATION, 'path' => '/path']),
        );
    }

    public function testPhpDriver(): void
    {
        static::assertInstanceOf(
            AbstractMappingDriver::class,
            $this->factory->getDriver(['type' => DriverFactory::DRIVER_PHP, 'path' => '/path']),
        );
    }

    public function testJsonDriver(): void
    {
        static::assertInstanceOf(
            AbstractMappingDriver::class,
            $this->factory->getDriver(['type' => DriverFactory::DRIVER_JSON, 'path' => '/path']),
        );
    }

    public function testXmlDriver(): void
    {
        static::assertInstanceOf(
            AbstractMappingDriver::class,
            $this->factory->getDriver(['type' => DriverFactory::DRIVER_XML, 'path' => '/path']),
        );
    }

    public function testYamlDriver(): void
    {
        static::assertInstanceOf(
            AbstractMappingDriver::class,
            $this->factory->getDriver(['type' => DriverFactory::DRIVER_YAML, 'path' => '/path']),
        );
    }
}
