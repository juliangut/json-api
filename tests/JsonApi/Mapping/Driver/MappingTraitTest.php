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

use Jgut\JsonApi\Mapping\Driver\MappingTrait;
use Jgut\JsonApi\Mapping\Metadata\AttributeMetadata;
use Jgut\JsonApi\Mapping\Metadata\IdentifierMetadata;
use Jgut\JsonApi\Mapping\Metadata\LinkMetadata;
use Jgut\JsonApi\Mapping\Metadata\RelationshipMetadata;
use Jgut\JsonApi\Mapping\Metadata\ResourceMetadata;
use Jgut\JsonApi\Schema\MetadataSchema;
use Jgut\Mapping\Exception\DriverException;
use PHPUnit\Framework\TestCase;

/**
 * Mapping definition trait tests.
 */
class MappingTraitTest extends TestCase
{
    public function testNoClass()
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('Resource class missing');

        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->will(self::returnValue([
                [],
            ]));
        /* @var MappingTrait $driver */

        $driver->getMetadata();
    }

    public function testNoRelationshipClass()
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('Resource relationship class missing');

        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->will(self::returnValue([
                [
                    'class' => 'ClassName',
                    'relationships' => [[]],
                ],
            ]));
        /* @var MappingTrait $driver */

        $driver->getMetadata();
    }

    public function testInvalidSchemaClass()
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessageRegExp('/Schema class ".+" does not exist or does not implement ".+"/');

        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->will(self::returnValue([
                [
                    'class' => 'ClassName',
                    'schemaClass' => self::class,
                ],
            ]));
        /* @var MappingTrait $driver */

        $driver->getMetadata();
    }

    public function testResources()
    {
        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->will(self::returnValue([
                [
                    'class' => 'My\Class',
                    'attributesInInclude' => false,
                    'schemaClass' => MetadataSchema::class,
                    'urlPrefix' => 'resource',
                    'id' => [
                        'name' => 'uuid',
                        'getter' => 'getUuid',
                    ],
                    'attributes' => [
                        [
                            'name' => 'attributeOne',
                            'groups' => ['one'],
                        ],
                        [
                            'name' => 'attributeTwo',
                        ],
                    ],
                    'relationships' => [
                        [
                            'class' => 'My\Class\Relationship\One',
                            'name' => 'relationshipOne',
                            'selfLinkIncluded' => true,
                            'links' => [
                                'custom' => [
                                    'name' => 'custom',
                                    'href' => '/custom',
                                ],
                            ],
                        ],
                        [
                            'class' => 'My\Class\Relationship\Two',
                            'name' => 'relationshipTwo',
                            'relatedLinkIncluded' => true,
                            'groups' => ['relationship', 'two'],
                        ],
                    ],
                ],
            ]));
        /* @var \Jgut\Mapping\Driver\AbstractMappingDriver $driver */

        $resources = $driver->getMetadata();

        /** @var ResourceMetadata $resource */
        $resource = $resources[0];
        self::assertInstanceOf(ResourceMetadata::class, $resource);
        self::assertEquals('My\Class', $resource->getClass());
        self::assertEquals('class', $resource->getName());
        self::assertEquals('resource', $resource->getUrlPrefix());
        self::assertEquals(MetadataSchema::class, $resource->getSchemaClass());
        self::assertInstanceOf(IdentifierMetadata::class, $resource->getIdentifier());
        self::assertEquals('My\Class', $resource->getIdentifier()->getClass());
        self::assertEquals('uuid', $resource->getIdentifier()->getName());
        self::assertEquals('getUuid', $resource->getIdentifier()->getGetter());

        $attributes = $resource->getAttributes();

        $attribute = $attributes['attributeOne'];
        self::assertInstanceOf(AttributeMetadata::class, $attribute);
        self::assertEquals('My\Class', $attribute->getClass());
        self::assertEquals('attributeOne', $attribute->getName());
        self::assertEquals('getAttributeOne', $attribute->getGetter());
        self::assertEquals('setAttributeOne', $attribute->getSetter());
        self::assertEquals(['one'], $attribute->getGroups());

        $attribute = $attributes['attributeTwo'];
        self::assertInstanceOf(AttributeMetadata::class, $attribute);
        self::assertEquals('My\Class', $attribute->getClass());
        self::assertEquals('attributeTwo', $attribute->getName());
        self::assertEquals('getAttributeTwo', $attribute->getGetter());
        self::assertEquals('setAttributeTwo', $attribute->getSetter());
        self::assertEquals([], $attribute->getGroups());

        $relationships = $resource->getRelationships();

        $relationship = $relationships['relationshipOne'];
        self::assertInstanceOf(RelationshipMetadata::class, $relationship);
        self::assertEquals('My\Class\Relationship\One', $relationship->getClass());
        self::assertEquals('relationshipOne', $relationship->getName());
        self::assertEquals('getRelationshipOne', $relationship->getGetter());
        self::assertEquals('setRelationshipOne', $relationship->getSetter());
        self::assertEquals([], $relationship->getGroups());
        self::assertFalse($relationship->isDefaultIncluded());
        self::assertTrue($relationship->isSelfLinkIncluded());
        self::assertFalse($relationship->isRelatedLinkIncluded());
        self::assertArrayHasKey('custom', $relationship->getLinks());
        self::assertInstanceOf(LinkMetadata::class, $relationship->getLinks()['custom']);

        $relationship = $relationships['relationshipTwo'];
        self::assertInstanceOf(RelationshipMetadata::class, $relationship);
        self::assertEquals('My\Class\Relationship\Two', $relationship->getClass());
        self::assertEquals('relationshipTwo', $relationship->getName());
        self::assertEquals('getRelationshipTwo', $relationship->getGetter());
        self::assertEquals('setRelationshipTwo', $relationship->getSetter());
        self::assertEquals(['relationship', 'two'], $relationship->getGroups());
        self::assertFalse($relationship->isDefaultIncluded());
        self::assertFalse($relationship->isSelfLinkIncluded());
        self::assertTrue($relationship->isRelatedLinkIncluded());
    }
}
