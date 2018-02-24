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
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Class "stdClass" is not a "Class"
     */
    public function testInvalidResource()
    {
        $metadata = new ResourceMetadata('Class', 'Resource');

        $schema = new MetadataSchema($this->factory, $metadata);

        $schema->getId(new \stdClass());
    }

    /**
     * @expectedException \RuntimeException
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
            ->setUrl('/resource');

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
            ->setUrl('/custom/resource');

        $schema = new MetadataSchema($this->factory, $metadata);

        $this->assertEquals('/custom/resource/aaa', $schema->getSelfSubUrl($resource));
    }

    public function testGetAttributes()
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

    public function testNoRelationship()
    {
        $metadata = (new ResourceMetadata(\stdClass::class, 'Resource'));

        $schema = new MetadataSchema($this->factory, $metadata);

        $this->assertEmpty($schema->getRelationships(new \stdClass(), true, []));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Requested include relationships "One", "Two" does not exist
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

    public function testRelationships()
    {
        $resource = new class() {
            public function getRelationship(): string
            {
                return 'aaa';
            }
        };

        $relationship = (new RelationshipMetadata(\stdClass::class, 'relationship'))
            ->setGetter('getRelationship');

        $metadata = (new ResourceMetadata(\get_class($resource), 'Resource'))
            ->addRelationship($relationship);

        $schema = new MetadataSchema($this->factory, $metadata);

        $relationships = $schema->getRelationships($resource, true, ['relationship' => null]);

        $this->assertTrue(isset($relationships['relationship']));
        $this->assertInstanceOf(\Closure::class, $relationships['relationship']['data']);
        $this->assertEquals('aaa', $relationships['relationship']['data']());
        $this->assertFalse($relationships['relationship']['showSelf']);
        $this->assertFalse($relationships['relationship']['related']);
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
