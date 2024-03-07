<?php

/*
 * (c) 2018-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Tests\Mapping\Metadata;

use Jgut\JsonApi\Mapping\Metadata\AttributeMetadata;
use Jgut\JsonApi\Mapping\Metadata\IdentifierMetadata;
use Jgut\JsonApi\Mapping\Metadata\RelationshipMetadata;
use Jgut\JsonApi\Mapping\Metadata\ResourceObjectMetadata;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 */
class ResourceObjectMetadataTest extends TestCase
{
    protected ResourceObjectMetadata $resource;

    protected function setUp(): void
    {
        $this->resource = new ResourceObjectMetadata('Class', 'Name');
    }

    public function testNoIdentifier(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Resource "Class" does not define an identifier.');

        $this->resource->getIdentifier();
    }

    public function testDefaults(): void
    {
        static::assertNull($this->resource->getSchema());
        static::assertNull($this->resource->getPrefix());
        static::assertEquals([], $this->resource->getAttributes());
        static::assertEquals([], $this->resource->getRelationships());
        static::assertNull($this->resource->getGroup());
    }

    public function testSchemaClass(): void
    {
        $this->resource->setSchema('class');

        static::assertEquals('class', $this->resource->getSchema());
    }

    public function testUrlPrefix(): void
    {
        $this->resource->setPrefix('resource');

        static::assertEquals('resource', $this->resource->getPrefix());
    }

    public function testIdentifier(): void
    {
        $identifier = new IdentifierMetadata(IdentifierMetadata::class, '');

        $this->resource->setIdentifier($identifier);

        static::assertEquals($identifier, $this->resource->getIdentifier());
    }

    public function testAttributes(): void
    {
        $attribute = new AttributeMetadata(AttributeMetadata::class, 'attribute');

        $this->resource->addAttribute($attribute);

        static::assertEquals(['attribute' => $attribute], $this->resource->getAttributes());
    }

    public function testRelationships(): void
    {
        $relationship = new RelationshipMetadata(RelationshipMetadata::class, 'relationship');

        $this->resource->addRelationship($relationship);

        static::assertEquals(['relationship' => $relationship], $this->resource->getRelationships());
    }

    public function testGroup(): void
    {
        $this->resource->setGroup('group');

        static::assertEquals('group', $this->resource->getGroup());
    }
}
