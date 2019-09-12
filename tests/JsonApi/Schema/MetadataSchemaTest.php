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

use Jgut\JsonApi\Encoding\Factory;
use Jgut\JsonApi\Exception\SchemaException;
use Jgut\JsonApi\Mapping\Metadata\AttributeMetadata;
use Jgut\JsonApi\Mapping\Metadata\IdentifierMetadata;
use Jgut\JsonApi\Mapping\Metadata\LinkMetadata;
use Jgut\JsonApi\Mapping\Metadata\RelationshipMetadata;
use Jgut\JsonApi\Mapping\Metadata\ResourceMetadata;
use Jgut\JsonApi\Schema\MetadataSchema;
use PHPUnit\Framework\TestCase;

/**
 * Routing compiler tests.
 */
class MetadataSchemaTest extends TestCase
{
    /**
     * @var Factory
     */
    protected $factory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->factory = $this->getMockBuilder(Factory::class)
            ->getMock();
    }

    public function testInvalidResource()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionMessage('Class "stdClass" is not a "Class"');

        $metadata = new ResourceMetadata('Class', 'Resource');

        $schema = new MetadataSchema($this->factory, $metadata);

        $schema->getId(new \stdClass());
    }

    public function testNoId()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionMessage('No id attribute defined for "stdClass" resource');

        $metadata = new ResourceMetadata(\stdClass::class, 'Resource');

        $schema = new MetadataSchema($this->factory, $metadata);

        $schema->getId(new \stdClass());
    }

    public function testGetId()
    {
        $resource = new class() {
            public function getId(): string
            {
                return 'aaa';
            }
        };

        $identifier = (new IdentifierMetadata(\stdClass::class, 'Id'))
            ->setGetter('getId');

        $metadata = (new ResourceMetadata(\get_class($resource), 'Resource'))
            ->setIdentifier($identifier)
            ->setUrlPrefix('/resource');

        $schema = new MetadataSchema($this->factory, $metadata);

        self::assertEquals('aaa', $schema->getId($resource));
    }

    public function testGetUrl()
    {
        $resource = new class() {
            public function getId(): string
            {
                return 'aaa';
            }
        };

        $identifier = (new IdentifierMetadata(\stdClass::class, 'Id'))
            ->setGetter('getId');

        $metadata = (new ResourceMetadata(\get_class($resource), 'Resource'))
            ->setIdentifier($identifier)
            ->setUrlPrefix('/custom/resource');

        $schema = new MetadataSchema($this->factory, $metadata);

        self::assertEquals('/custom/resource/aaa', $schema->getSelfSubUrl($resource));
    }

    public function testUnkownAttribute()
    {
        $resource = new class() {
            public function getAttribute(): string
            {
                return 'aaa';
            }
        };

        $attribute = (new AttributeMetadata(\stdClass::class, 'attribute'))
            ->setGetter('getAttribute');

        $metadata = (new ResourceMetadata(\get_class($resource), 'Resource'))
            ->addAttribute($attribute);

        $schema = new MetadataSchema($this->factory, $metadata);

        self::assertEquals(['attribute' => 'aaa'], $schema->getAttributes($resource, ['attribute']));
    }

    public function testGetAttributes()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionMessage('Requested attribute "unknown" does not exist');

        $resource = new class() {
            public function getAttributeA(): string
            {
                return 'aaa';
            }

            public function getAttributeB(): string
            {
                return 'bbb';
            }
        };

        $attributeA = (new AttributeMetadata(\stdClass::class, 'attributeA'))
            ->setGetter('getAttributeA')
            ->setGroups(['test']);
        $attributeB = (new AttributeMetadata(\stdClass::class, 'attributeB'))
            ->setGetter('getAttributeB')
            ->setGroups(['none']);

        $metadata = (new ResourceMetadata(\get_class($resource), 'Resource'))
            ->addAttribute($attributeA)
            ->addAttribute($attributeB)
            ->setGroup('test');

        $schema = new MetadataSchema($this->factory, $metadata);

        self::assertEquals(['attributeA' => 'aaa'], $schema->getAttributes($resource, ['unknown']));
    }

    public function testNoRelationship()
    {
        $metadata = new ResourceMetadata(\stdClass::class, 'Resource');

        $schema = new MetadataSchema($this->factory, $metadata);

        self::assertEmpty($schema->getRelationships(new \stdClass(), true, []));
    }

    public function testUnknownRelationship()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionMessage('Requested relationships "One", "Two" does not exist');

        $resource = new class() {
            public function getAttribute(): string
            {
                return 'aaa';
            }
        };

        $relationship = (new RelationshipMetadata(\stdClass::class, 'relationship'))
            ->setGetter('getRelationship');

        $metadata = (new ResourceMetadata(\get_class($resource), 'Resource'))
            ->addRelationship($relationship);

        $schema = new MetadataSchema($this->factory, $metadata);

        $schema->getRelationships($resource, true, ['One' => null, 'Two' => null]);
    }

    public function testRelationshipsWithLinks()
    {
        $resource = new class() {
            public function getRelationshipA(): string
            {
                return 'aaa';
            }
        };

        $link = (new LinkMetadata('custom'))->setHref('/custom');

        $relationshipA = (new RelationshipMetadata(\stdClass::class, 'relationshipA'))
            ->setGetter('getRelationshipA')
            ->setSelfLinkIncluded(true)
            ->setRelatedLinkIncluded(true)
            ->addLink($link);

        $metadata = (new ResourceMetadata(\get_class($resource), 'Resource'))
            ->addRelationship($relationshipA);

        $schema = new MetadataSchema($this->factory, $metadata);

        $relationships = $schema->getRelationships($resource, true, ['relationshipA' => null]);

        self::assertTrue(isset($relationships['relationshipA']));
        self::assertFalse($relationships['relationshipA']['showData']);
        self::assertTrue($relationships['relationshipA']['showSelf']);
        self::assertFalse($relationships['relationshipA']['showRelated']);
        self::assertArrayHasKey('custom', $relationships['relationshipA']['links']);
    }

    public function testRelationshipsWithData()
    {
        $resource = new class() {
            public function getRelationshipA(): string
            {
                return 'aaa';
            }

            public function getRelationshipB(): string
            {
                return 'bbb';
            }
        };

        $relationshipA = (new RelationshipMetadata(\stdClass::class, 'relationshipA'))
            ->setGetter('getRelationshipA')
            ->setGroups(['test']);
        $relationshipB = (new RelationshipMetadata(\stdClass::class, 'relationshipB'))
            ->setGetter('getRelationshipB')
            ->setGroups(['none']);

        $metadata = (new ResourceMetadata(\get_class($resource), 'Resource'))
            ->addRelationship($relationshipA)
            ->addRelationship($relationshipB)
            ->setGroup('test');

        $schema = new MetadataSchema($this->factory, $metadata);

        $relationships = $schema->getRelationships($resource, true, ['relationshipA' => null]);

        self::assertTrue(isset($relationships['relationshipA']));
        self::assertInstanceOf(\Closure::class, $relationships['relationshipA']['data']);
        self::assertEquals('aaa', $relationships['relationshipA']['data']());
        self::assertFalse(isset($relationships['relationshipA']['showData']));
        self::assertFalse(isset($relationships['relationshipA']['showSelf']));
        self::assertFalse(isset($relationships['relationshipA']['related']));
        self::assertFalse(isset($relationships['relationshipB']));
    }

    public function testIncludePaths()
    {
        $relationship = (new RelationshipMetadata(\stdClass::class, 'Relationship'))
            ->setGetter('getRelationship')
            ->setDefaultIncluded(true);

        $metadata = (new ResourceMetadata(\stdClass::class, 'Resource'))
            ->addRelationship($relationship);

        $schema = new MetadataSchema($this->factory, $metadata);

        self::assertEquals(['Relationship'], $schema->getIncludePaths());
    }
}
