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

namespace Jgut\JsonApi\Tests\Schema;

use InvalidArgumentException;
use Jgut\JsonApi\Configuration;
use Jgut\JsonApi\Encoding\Factory;
use Jgut\JsonApi\Mapping\Metadata\ResourceObjectMetadata;
use Jgut\JsonApi\Schema\MetadataSchemaInterface;
use Jgut\JsonApi\Schema\Resolver;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ResolverTest extends TestCase
{
    protected Resolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new Resolver(new Configuration());
    }

    public function testInvalidSchemaClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/^Schema class .+ must implement .+\.$/');

        $factory = $this->getMockBuilder(Factory::class)
            ->getMock();
        /** @var Factory $factory */
        $resource = (new ResourceObjectMetadata('Class', 'Name'))
            ->setSchema(self::class);

        $schemaFactory = $this->resolver->getSchemaFactory($resource);

        $schemaFactory($factory);
    }

    public function testSchemaFactory(): void
    {
        $factory = $this->getMockBuilder(Factory::class)
            ->getMock();
        /** @var Factory $factory */
        $resource = new ResourceObjectMetadata('Class', 'Name');

        $schemaFactory = $this->resolver->getSchemaFactory($resource);

        static::assertInstanceOf(MetadataSchemaInterface::class, $schemaFactory($factory));
    }
}
