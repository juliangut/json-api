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
use Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
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

    public function testNoId(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Resource "stdClass" does not define an id attribute');

        $metadata = new ResourceMetadata(\stdClass::class, 'Resource');

        $schema = new MetadataSchema($this->factory, $metadata);

        $schema->getId(new \stdClass());
    }

    public function testInvalidResource(): void
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionMessage('Class "stdClass" is not a "Class"');

        $metadata = new ResourceMetadata('Class', 'Resource');
        $metadata->setIdentifier(new IdentifierMetadata('Class', 'id'));

        $schema = new MetadataSchema($this->factory, $metadata);

        $schema->getId(new \stdClass());
    }

    public function testGetType(): void
    {
        $metadata = (new ResourceMetadata(\stdClass::class, 'Resource'));

        $schema = new MetadataSchema($this->factory, $metadata);

        self::assertEquals('Resource', $schema->getType());
    }

    public function testGetId(): void
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
            ->setIdentifier($identifier);

        $schema = new MetadataSchema($this->factory, $metadata);

        self::assertEquals('aaa', $schema->getId($resource));
    }

    public function testGetUrl(): void
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

        $schema = new MetadataSchema(new Factory(), $metadata);

        self::assertEquals('/custom/resource/aaa', $schema->getSelfLink($resource)->getStringRepresentation(''));
    }

    public function testGetAttributes(): void
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

        self::assertArrayHasKey('attribute', $schema->getAttributes($resource));
        self::assertInstanceOf(\Closure::class, $schema->getAttributes($resource)['attribute']);
        self::assertEquals('aaa', $schema->getAttributes($resource)['attribute']());
    }

    public function testNoRelationship(): void
    {
        $metadata = new ResourceMetadata(\stdClass::class, 'Resource');

        $schema = new MetadataSchema($this->factory, $metadata);

        self::assertEmpty($schema->getRelationships(new \stdClass()));
    }

    public function testGetRelationships(): void
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
            ->setGroups(['test'])
            ->addLink(new LinkMetadata('me', '/me'))
            ->setMeta(['meta' => 'data']);
        $relationshipB = (new RelationshipMetadata(\stdClass::class, 'relationshipB'))
            ->setGetter('getRelationshipB')
            ->setGroups(['none']);

        $metadata = (new ResourceMetadata(\get_class($resource), 'Resource'))
            ->addRelationship($relationshipA)
            ->addRelationship($relationshipB)
            ->setGroup('test');

        $schema = new MetadataSchema($this->factory, $metadata);

        $relationships = $schema->getRelationships($resource);

        self::assertTrue(isset($relationships['relationshipA']));
        self::assertInstanceOf(\Closure::class, $relationships['relationshipA'][SchemaInterface::RELATIONSHIP_DATA]);
        self::assertEquals('aaa', $relationships['relationshipA'][SchemaInterface::RELATIONSHIP_DATA]());
        self::assertFalse(isset($relationships['relationshipA'][SchemaInterface::RELATIONSHIP_LINKS_SELF]));
        self::assertFalse(isset($relationships['relationshipA'][SchemaInterface::RELATIONSHIP_LINKS_RELATED]));
        self::assertTrue(isset($relationships['relationshipA'][SchemaInterface::RELATIONSHIP_LINKS]));
        self::assertTrue(isset($relationships['relationshipA'][SchemaInterface::RELATIONSHIP_META]));
        self::assertFalse(isset($relationships['relationshipB']));
    }

    public function testGetLinks(): void
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
            ->addLink(new LinkMetadata('me', 'https://example.com/me'))
            ->setSelfLinkIncluded(false)
            ->setRelatedLinkIncluded(false);

        $schema = new MetadataSchema(new Factory(), $metadata);

        self::assertArrayHasKey(LinkInterface::SELF, $schema->getLinks($resource));
        self::assertArrayHasKey('me', $schema->getLinks($resource));
        self::assertFalse($schema->isAddSelfLinkInRelationshipByDefault('relationship'));
        self::assertFalse($schema->isAddRelatedLinkInRelationshipByDefault('relationship'));
    }

    public function testGetMeta(): void
    {
        $resource = new class() {
            public function getId(): string
            {
                return 'aaa';
            }
        };

        $identifier = (new IdentifierMetadata(\stdClass::class, 'Id'))
            ->setGetter('getId')
            ->setMeta(['meta' => 'data']);

        $metadata = (new ResourceMetadata(\get_class($resource), 'Resource'))
            ->setIdentifier($identifier)
            ->setMeta(['meta' => 'value']);

        $schema = new MetadataSchema(new Factory(), $metadata);

        self::assertTrue($schema->hasIdentifierMeta($resource));
        self::assertEquals(['meta' => 'data'], $schema->getIdentifierMeta($resource));
        self::assertTrue($schema->hasResourceMeta($resource));
        self::assertEquals(['meta' => 'value'], $schema->getResourceMeta($resource));
    }
}
