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
     * @expectedExceptionMessage No id attribute defined for "Class" resource
     */
    public function testNoId()
    {
        $metadata = new ResourceMetadata('Class', 'Resource');

        $schema = new MetadataSchema($this->factory, $metadata);

        $schema->getId(new \stdClass());
    }

    public function testGetId()
    {
        $identifier = (new AttributeMetadata('Class', 'Id'))
            ->setGetter('getId');

        $metadata = (new ResourceMetadata('Class', 'Resource'))
            ->setIdentifier($identifier);

        $schema = new MetadataSchema($this->factory, $metadata);

        $resource = new class() {
            public function getId(): string
            {
                return 'aaa';
            }
        };

        $this->assertEquals('aaa', $schema->getId($resource));
    }

    public function testGetAttributes()
    {
        $attribute = (new AttributeMetadata('Class', 'Attribute'))
            ->setGetter('getAttribute');

        $metadata = (new ResourceMetadata('Class', 'Resource'))
            ->addAttribute($attribute);

        $schema = new MetadataSchema($this->factory, $metadata);

        $resource = new class() {
            public function getAttribute(): string
            {
                return 'aaa';
            }
        };

        $this->assertEquals(['Attribute' => 'aaa'], $schema->getAttributes($resource));
    }

    public function testNoRelationship()
    {
        $metadata = (new ResourceMetadata('Class', 'Resource'));

        $schema = new MetadataSchema($this->factory, $metadata);

        $this->assertEmpty($schema->getRelationships(new \stdClass(), true, []));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Requested include relationships "One", "Two" does not exist
     */
    public function testUnknownRelationship()
    {
        $relationship = (new RelationshipMetadata('Class', 'Relationship'))
            ->setGetter('getRelationship');

        $metadata = (new ResourceMetadata('Class', 'Resource'))
            ->addRelationship($relationship);

        $schema = new MetadataSchema($this->factory, $metadata);

        $resource = new class() {
            public function getRelationship(): string
            {
                return 'aaa';
            }
        };

        $relationships = $schema->getRelationships($resource, true, ['One' => null, 'Two' => null]);
    }

    public function testRelationships()
    {
        $relationship = (new RelationshipMetadata('Class', 'Relationship'))
            ->setGetter('getRelationship');

        $metadata = (new ResourceMetadata('Class', 'Resource'))
            ->addRelationship($relationship);

        $schema = new MetadataSchema($this->factory, $metadata);

        $resource = new class() {
            public function getRelationship(): string
            {
                return 'aaa';
            }
        };

        $relationships = $schema->getRelationships($resource, true, ['Relationship' => null]);

        $this->assertTrue(isset($relationships['Relationship']));
        $this->assertInstanceOf(\Closure::class, $relationships['Relationship']['data']);
        $this->assertEquals('aaa', $relationships['Relationship']['data']());
        $this->assertFalse($relationships['Relationship']['showSelf']);
        $this->assertFalse($relationships['Relationship']['related']);
    }

    public function testIncludePaths()
    {
        $relationship = (new RelationshipMetadata('Class', 'Relationship'))
            ->setGetter('getRelationship')
            ->setDefaultIncluded(true);

        $metadata = (new ResourceMetadata('Class', 'Resource'))
            ->addRelationship($relationship);

        $schema = new MetadataSchema($this->factory, $metadata);

        $this->assertEquals(['Relationship'], $schema->getIncludePaths());
    }
}
