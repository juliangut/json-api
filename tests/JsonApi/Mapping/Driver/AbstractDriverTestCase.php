<?php

/*
 * (c) 2018-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Tests\Mapping\Driver;

use Jgut\JsonApi\Mapping\Driver\AnnotationDriver;
use Jgut\JsonApi\Mapping\Driver\AttributeDriver;
use Jgut\JsonApi\Mapping\Driver\JsonDriver;
use Jgut\JsonApi\Mapping\Driver\PhpDriver;
use Jgut\JsonApi\Mapping\Driver\XmlDriver;
use Jgut\JsonApi\Mapping\Driver\YamlDriver;
use Jgut\JsonApi\Mapping\Metadata\AttributeMetadata;
use Jgut\JsonApi\Mapping\Metadata\IdentifierMetadata;
use Jgut\JsonApi\Mapping\Metadata\LinkMetadata;
use Jgut\JsonApi\Mapping\Metadata\RelationshipMetadata;
use Jgut\JsonApi\Mapping\Metadata\ResourceObjectMetadata;
use Jgut\JsonApi\Schema\MetadataSchema;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
abstract class AbstractDriverTestCase extends TestCase
{
    /**
     * @param AttributeDriver|PhpDriver|JsonDriver|XmlDriver|YamlDriver|AnnotationDriver $driver
     */
    protected function checkResources($driver): void
    {
        $resources = $driver->getMetadata();

        $resource = $resources[0];
        static::assertInstanceOf(ResourceObjectMetadata::class, $resource);
        static::assertEquals('resourceA', $resource->getName());
        static::assertEquals(MetadataSchema::class, $resource->getSchema());
        static::assertEquals('resource', $resource->getPrefix());
        static::assertTrue($resource->isLinkSelf());
        static::assertNull($resource->isLinkRelated());
        static::assertEquals([], $resource->getLinks());
        static::assertEquals(['first' => 'firstValue', 'second' => 'secondValue'], $resource->getMeta());

        $identifier = $resource->getIdentifier();
        static::assertInstanceOf(IdentifierMetadata::class, $identifier);
        static::assertEquals('id', $identifier->getName());
        static::assertEquals('getId', $identifier->getGetter());
        static::assertEquals('setId', $identifier->getSetter());
        static::assertEquals([], $identifier->getMeta());

        $attributes = $resource->getAttributes();
        static::assertCount(1, $attributes);
        static::assertArrayHasKey('theOne', $attributes);
        $attribute = $attributes['theOne'];
        static::assertInstanceOf(AttributeMetadata::class, $attribute);
        static::assertEquals('theOne', $attribute->getName());
        static::assertEquals([], $attribute->getGroups());
        static::assertEquals('isOne', $attribute->getGetter());
        static::assertEquals('setOne', $attribute->getSetter());

        $relationships = $resource->getRelationships();
        static::assertCount(1, $relationships);
        static::assertArrayHasKey('relative', $relationships);
        $relationship = $relationships['relative'];
        static::assertInstanceOf(RelationshipMetadata::class, $relationship);
        static::assertEquals('relative', $relationship->getName());
        static::assertEquals([], $relationship->getGroups());
        static::assertEquals('getRelative', $relationship->getGetter());
        static::assertEquals('setRelative', $relationship->getSetter());
        static::assertEquals(['key' => 'value'], $relationship->getMeta());

        $relationshipLinks = $relationship->getLinks();
        static::assertCount(1, $relationshipLinks);
        static::assertArrayHasKey('custom', $relationshipLinks);
        $relationshipLink = $relationshipLinks['custom'];
        static::assertInstanceOf(LinkMetadata::class, $relationshipLink);
        static::assertEquals('/custom/path', $relationshipLink->getHref());
        static::assertEquals('custom', $relationshipLink->getTitle());
        static::assertEquals(['key' => 'path'], $relationshipLink->getMeta());

        $resource = $resources[1];
        static::assertInstanceOf(ResourceObjectMetadata::class, $resource);
        static::assertEquals('resourceTwo', $resource->getName());
        static::assertNull($resource->getSchema());
        static::assertNull($resource->getPrefix());
        static::assertNull($resource->isLinkSelf());
        static::assertFalse($resource->isLinkRelated());
        static::assertEquals([], $resource->getMeta());

        $resourceLinks = $resource->getLinks();
        static::assertCount(2, $resourceLinks);
        static::assertArrayHasKey('me', $resourceLinks);
        static::assertArrayHasKey('you', $resourceLinks);
        $resourceLink = $resourceLinks['me'];
        static::assertInstanceOf(LinkMetadata::class, $resourceLink);
        static::assertEquals('/me', $resourceLink->getHref());
        static::assertEquals('me', $resourceLink->getTitle());
        static::assertEquals([], $resourceLink->getMeta());
        $resourceLink = $resourceLinks['you'];
        static::assertInstanceOf(LinkMetadata::class, $resourceLink);
        static::assertEquals('/you', $resourceLink->getHref());
        static::assertEquals('you', $resourceLink->getTitle());
        static::assertEquals([], $resourceLink->getMeta());

        $identifier = $resource->getIdentifier();
        static::assertInstanceOf(IdentifierMetadata::class, $identifier);
        static::assertEquals('uuid', $identifier->getName());
        static::assertEquals('getUuid', $identifier->getGetter());
        static::assertEquals('setUuid', $identifier->getSetter());

        $attributes = $resource->getAttributes();
        static::assertCount(1, $attributes);
        static::assertArrayHasKey('two', $attributes);
        $attribute = $attributes['two'];
        static::assertInstanceOf(AttributeMetadata::class, $attribute);
        static::assertEquals('two', $attribute->getName());
        static::assertEquals(['read'], $attribute->getGroups());
        static::assertEquals('getTwo', $attribute->getGetter());
        static::assertEquals('setTwo', $attribute->getSetter());
    }
}
