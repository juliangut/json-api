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
use Jgut\JsonApi\Mapping\Metadata\RelationshipMetadata;
use Jgut\JsonApi\Mapping\Metadata\ResourceMetadata;
use Jgut\JsonApi\Schema\MetadataSchema;
use PHPUnit\Framework\TestCase;

/**
 * Mapping definition trait tests.
 */
class MappingTraitTest extends TestCase
{
    /**
     * @expectedException \Jgut\Mapping\Exception\DriverException
     * @expectedExceptionMessage Resource class missing
     */
    public function testNoClass()
    {
        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects($this->once())
            ->method('getMappingData')
            ->will($this->returnValue([
                [],
            ]));
        /* @var MappingTrait $driver */

        $driver->getMetadata();
    }

    /**
     * @expectedException \Jgut\Mapping\Exception\DriverException
     * @expectedExceptionMessage Resource relationship class missing
     */
    public function testNoRelationshipClass()
    {
        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects($this->once())
            ->method('getMappingData')
            ->will($this->returnValue([
                [
                    'class' => 'ClassName',
                    'relationships' => [[]],
                ],
            ]));
        /* @var MappingTrait $driver */

        $driver->getMetadata();
    }

    /**
     * @expectedException \Jgut\Mapping\Exception\DriverException
     * @expectedExceptionMessageRegExp /Schema class ".+" does not exist or does not implement ".+"/
     */
    public function testInvalidSchemaClass()
    {
        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects($this->once())
            ->method('getMappingData')
            ->will($this->returnValue([
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
        $driver->expects($this->once())
            ->method('getMappingData')
            ->will($this->returnValue([
                [
                    'class' => 'My\Class',
                    'attributesInInclude' => false,
                    'schemaClass' => MetadataSchema::class,
                    'urlPrefix' => 'resource',
                    'id' => [
                        'name' => 'uuid',
                        'setter' => 'setUuid',
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
                                'custom' => '/custom',
                            ],
                        ],
                        [
                            'class' => 'My\Class\Relationship\Two',
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
        self::assertEquals('/resource', $resource->getUrlPrefix());
        self::assertEquals(MetadataSchema::class, $resource->getSchemaClass());
        self::assertInstanceOf(AttributeMetadata::class, $resource->getIdentifier());
        self::assertEquals('My\Class', $resource->getIdentifier()->getClass());
        self::assertEquals('uuid', $resource->getIdentifier()->getName());
        self::assertEquals('getUuid', $resource->getIdentifier()->getGetter());
        self::assertEquals('setUuid', $resource->getIdentifier()->getSetter());
        self::assertEquals([], $resource->getIdentifier()->getGroups());

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
        self::assertEquals(['custom' => '/custom'], $relationship->getLinks());

        $relationship = $relationships['two'];
        self::assertInstanceOf(RelationshipMetadata::class, $relationship);
        self::assertEquals('My\Class\Relationship\Two', $relationship->getClass());
        self::assertEquals('two', $relationship->getName());
        self::assertEquals('getTwo', $relationship->getGetter());
        self::assertEquals('setTwo', $relationship->getSetter());
        self::assertEquals(['relationship', 'two'], $relationship->getGroups());
        self::assertFalse($relationship->isDefaultIncluded());
        self::assertFalse($relationship->isSelfLinkIncluded());
        self::assertTrue($relationship->isRelatedLinkIncluded());
    }
}
