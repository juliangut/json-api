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
use Jgut\JsonApi\Mapping\Metadata\IdentifierMetadata;
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
    protected function setUp(): void
    {
        $this->resource = new ResourceMetadata('Class', 'Name');
    }

    public function testNoIdentifier(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Resource "Class" does not define an id attribute');

        $this->resource->getIdentifier();
    }

    public function testDefaults(): void
    {
        self::assertNull($this->resource->getSchemaClass());
        self::assertNull($this->resource->getUrlPrefix());
        self::assertEquals([], $this->resource->getAttributes());
        self::assertEquals([], $this->resource->getRelationships());
        self::assertNull($this->resource->getGroup());
    }

    public function testSchemaClass(): void
    {
        $this->resource->setSchemaClass('class');

        self::assertEquals('class', $this->resource->getSchemaClass());
    }

    public function testUrlPrefix(): void
    {
        $this->resource->setUrlPrefix('resource');

        self::assertEquals('resource', $this->resource->getUrlPrefix());
    }

    public function testIdentifier(): void
    {
        $identifier = $this->getMockBuilder(IdentifierMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var IdentifierMetadata $identifier */

        $this->resource->setIdentifier($identifier);

        self::assertEquals($identifier, $this->resource->getIdentifier());
    }

    public function testAttributes(): void
    {
        $attribute = $this->getMockBuilder(AttributeMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attribute->expects(self::once())
            ->method('getName')
            ->will(self::returnValue('attribute'));
        /* @var AttributeMetadata $attribute */

        $this->resource->addAttribute($attribute);

        self::assertEquals(['attribute' => $attribute], $this->resource->getAttributes());
    }

    public function testRelationships(): void
    {
        $relationship = $this->getMockBuilder(RelationshipMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $relationship->expects(self::once())
            ->method('getName')
            ->will(self::returnValue('relationship'));
        /* @var RelationshipMetadata $relationship */

        $this->resource->addRelationship($relationship);

        self::assertEquals(['relationship' => $relationship], $this->resource->getRelationships());
    }

    public function testGroup(): void
    {
        $this->resource->setGroup('group');

        self::assertEquals('group', $this->resource->getGroup());
    }
}
