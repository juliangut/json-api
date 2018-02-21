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

namespace Jgut\JsonApi\Tests\Mapping\Metadata;

use Jgut\JsonApi\Mapping\Metadata\AttributeMetadata;
use Jgut\JsonApi\Mapping\Metadata\RelationshipMetadata;
use Jgut\JsonApi\Mapping\Metadata\ResourceMetadata;
use PHPUnit\Framework\TestCase;

/**
 * Resource metadata tests.
 */
class ResourceMetadataTest extends TestCase
{
    /**
     * @var ResourceMetadata
     */
    protected $resource;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->resource = new ResourceMetadata('Class', 'Name');
    }

    public function testDefaults()
    {
        self::assertNull($this->resource->getSchemaClass());
        self::assertNull($this->resource->getIdentifier());
        self::assertTrue($this->resource->isIncludeAttributes());
        self::assertEquals([], $this->resource->getAttributes());
        self::assertEquals([], $this->resource->getRelationships());
    }

    public function testSchemaClass()
    {
        $this->resource->setSchemaClass('class');

        self::assertEquals('class', $this->resource->getSchemaClass());
    }

    public function testIdentifier()
    {
        $identifier = $this->getMockBuilder(AttributeMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var AttributeMetadata $identifier */

        $this->resource->setIdentifier($identifier);

        self::assertEquals($identifier, $this->resource->getIdentifier());
    }

    public function testIncludeAttributes()
    {
        $this->resource->setIncludeAttributes(false);

        self::assertFalse($this->resource->isIncludeAttributes());
    }

    public function testAttributes()
    {
        $attribute = $this->getMockBuilder(AttributeMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attribute->expects(self::once())
            ->method('getName')
            ->will($this->returnValue('attribute'));
        /* @var AttributeMetadata $attribute */

        $this->resource->addAttribute($attribute);

        self::assertEquals(['attribute' => $attribute], $this->resource->getAttributes());
    }

    public function testRelationships()
    {
        $relationship = $this->getMockBuilder(RelationshipMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $relationship->expects(self::once())
            ->method('getName')
            ->will($this->returnValue('relationship'));
        /* @var RelationshipMetadata $relationship */

        $this->resource->addRelationship($relationship);

        self::assertEquals(['relationship' => $relationship], $this->resource->getRelationships());
    }
}
