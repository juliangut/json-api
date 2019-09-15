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

use Jgut\JsonApi\Configuration;
use Jgut\JsonApi\Encoding\Factory;
use Jgut\JsonApi\Mapping\Metadata\ResourceMetadata;
use Jgut\JsonApi\Schema\MetadataSchemaInterface;
use Jgut\JsonApi\Schema\Resolver;
use PHPUnit\Framework\TestCase;

/**
 * Schema resolver tests.
 */
class ResolverTest extends TestCase
{
    /**
     * @var Resolver
     */
    protected $resolver;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->resolver = new Resolver(new Configuration());
    }

    public function testInvalidSchemaClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/Schema class .+ must implement .+/');

        $factory = $this->getMockBuilder(Factory::class)
            ->getMock();
        /** @var Factory $factory */
        $resource = (new ResourceMetadata('Class', 'Name'))
            ->setSchemaClass(self::class);

        $schemaFactory = $this->resolver->getSchemaFactory($resource);

        $schemaFactory($factory);
    }

    public function testSchemaFactory(): void
    {
        $factory = $this->getMockBuilder(Factory::class)
            ->getMock();
        /** @var Factory $factory */
        $resource = new ResourceMetadata('Class', 'Name');

        $schemaFactory = $this->resolver->getSchemaFactory($resource);

        self::assertInstanceOf(MetadataSchemaInterface::class, $schemaFactory($factory));
    }
}
