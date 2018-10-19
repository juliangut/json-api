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
use Jgut\JsonApi\Mapping\Metadata\AttributeMetadata;
use Jgut\JsonApi\Mapping\Metadata\RelationshipMetadata;
use Jgut\JsonApi\Mapping\Metadata\ResourceMetadata;
use Jgut\JsonApi\Schema\MetadataSchema;
use Neomerx\JsonApi\Document\Link;
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
    protected function setUp()
    {
        $this->factory = $this->getMockBuilder(Factory::class)
            ->getMock();
    }

    /**
     * @expectedException \Jgut\JsonApi\Exception\SchemaException
     * @expectedExceptionMessage Class "stdClass" is not a "Class"
     */
    public function testInvalidResource()
    {
        $metadata = new ResourceMetadata('Class', 'Resource');

        $schema = new MetadataSchema($this->factory, $metadata);

        $schema->getId(new \stdClass());
    }

    /**
     * @expectedException \Jgut\JsonApi\Exception\SchemaException
     * @expectedExceptionMessage No id attribute defined for "stdClass" resource
     */
    public function testNoId()
    {
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

        $identifier = (new AttributeMetadata(\stdClass::class, 'Id'))
            ->setGetter('getId');

        $metadata = (new ResourceMetadata(\get_class($resource), 'Resource'))
            ->setIdentifier($identifier)
            ->setUrlPrefix('/resource');

        $schema = new MetadataSchema($this->factory, $metadata);

        $this->assertEquals('aaa', $schema->getId($resource));
    }

    public function testGetUrl()
    {
        $resource = new class() {
            public function getId(): string
            {
                return 'aaa';
            }
        };

        $identifier = (new AttributeMetadata(\stdClass::class, 'Id'))
            ->setGetter('getId');

        $metadata = (new ResourceMetadata(\get_class($resource), 'Resource'))
            ->setIdentifier($identifier)
            ->setUrlPrefix('/custom/resource');

        $schema = new MetadataSchema($this->factory, $metadata);

        $this->assertEquals('/custom/resource/aaa', $schema->getSelfSubUrl($resource));
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

        $this->assertEquals(['attribute' => 'aaa'], $schema->getAttributes($resource, ['attribute']));
    }

    /**
     * @expectedException \Jgut\JsonApi\Exception\SchemaException
     * @expectedExceptionMessage Requested attribute "unknown" does not exist
     */
    public function testGetAttributes()
    {
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

        $this->assertEquals(['attributeA' => 'aaa'], $schema->getAttributes($resource, ['unknown']));
    }

    public function testNoRelationship()
    {
        $metadata = (new ResourceMetadata(\stdClass::class, 'Resource'));

        $schema = new MetadataSchema($this->factory, $metadata);

        $this->assertEmpty($schema->getRelationships(new \stdClass(), true, []));
    }

    /**
     * @expectedException \Jgut\JsonApi\Exception\SchemaException
     * @expectedExceptionMessage Requested relationships "One", "Two" does not exist
     */
    public function testUnknownRelationship()
    {
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

        $relationshipA = (new RelationshipMetadata(\stdClass::class, 'relationshipA'))
            ->setGetter('getRelationshipA')
            ->setSelfLinkIncluded(true)
            ->setRelatedLinkIncluded(true)
            ->setLinks([
                'custom' => '/custom',
                'external' => 'http://example.com',
            ]);

        $metadata = (new ResourceMetadata(\get_class($resource), 'Resource'))
            ->addRelationship($relationshipA);

        $schema = new MetadataSchema($this->factory, $metadata);

        $relationships = $schema->getRelationships($resource, true, ['relationshipA' => null]);

        $this->assertTrue(isset($relationships['relationshipA']));
        $this->assertFalse($relationships['relationshipA']['showData']);
        $this->assertTrue($relationships['relationshipA']['showSelf']);
        $this->assertFalse($relationships['relationshipA']['showRelated']);
        $this->assertEquals('/custom', $relationships['relationshipA']['links']['custom']);
        $this->assertInstanceOf(Link::class, $relationships['relationshipA']['links']['external']);
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

        $this->assertTrue(isset($relationships['relationshipA']));
        $this->assertInstanceOf(\Closure::class, $relationships['relationshipA']['data']);
        $this->assertEquals('aaa', $relationships['relationshipA']['data']());
        $this->assertFalse(isset($relationships['relationshipA']['showData']));
        $this->assertFalse(isset($relationships['relationshipA']['showSelf']));
        $this->assertFalse(isset($relationships['relationshipA']['related']));
        $this->assertFalse(isset($relationships['relationshipB']));
    }

    public function testIncludePaths()
    {
        $relationship = (new RelationshipMetadata(\stdClass::class, 'Relationship'))
            ->setGetter('getRelationship')
            ->setDefaultIncluded(true);

        $metadata = (new ResourceMetadata(\stdClass::class, 'Resource'))
            ->addRelationship($relationship);

        $schema = new MetadataSchema($this->factory, $metadata);

        $this->assertEquals(['Relationship'], $schema->getIncludePaths());
    }
}
