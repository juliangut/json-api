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

use Closure;
use Jgut\JsonApi\Encoding\Factory;
use Jgut\JsonApi\Exception\SchemaException;
use Jgut\JsonApi\Mapping\Metadata\AttributeMetadata;
use Jgut\JsonApi\Mapping\Metadata\IdentifierMetadata;
use Jgut\JsonApi\Mapping\Metadata\LinkMetadata;
use Jgut\JsonApi\Mapping\Metadata\RelationshipMetadata;
use Jgut\JsonApi\Mapping\Metadata\ResourceObjectMetadata;
use Jgut\JsonApi\Schema\MetadataSchema;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

/**
 * @internal
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MetadataSchemaTest extends TestCase
{
    protected Factory $factory;

    protected function setUp(): void
    {
        $this->factory = $this->getMockBuilder(Factory::class)
            ->getMock();
    }

    public function testNoId(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Resource "stdClass" does not define an identifier.');

        $metadata = new ResourceObjectMetadata(stdClass::class, 'Resource');

        $schema = new MetadataSchema($this->factory, $metadata);

        $schema->getId(new stdClass());
    }

    public function testInvalidResource(): void
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionMessage('Class "stdClass" is not a "Class".');

        $metadata = new ResourceObjectMetadata('Class', 'Resource');
        $metadata->setIdentifier(new IdentifierMetadata('Class', 'id'));

        $schema = new MetadataSchema($this->factory, $metadata);

        $schema->getId(new stdClass());
    }

    public function testGetType(): void
    {
        $metadata = new ResourceObjectMetadata(stdClass::class, 'Resource');

        $schema = new MetadataSchema($this->factory, $metadata);

        static::assertEquals('Resource', $schema->getType());
    }

    public function testGetId(): void
    {
        $resource = new class() {
            public function getId(): string
            {
                return 'aaa';
            }
        };

        $identifier = (new IdentifierMetadata(stdClass::class, 'Id'))
            ->setGetter('getId');

        $metadata = (new ResourceObjectMetadata(\get_class($resource), 'Resource'))
            ->setIdentifier($identifier);

        $schema = new MetadataSchema($this->factory, $metadata);

        static::assertEquals('aaa', $schema->getId($resource));
    }

    public function testGetUrl(): void
    {
        $resource = new class() {
            public function getId(): string
            {
                return 'aaa';
            }
        };

        $identifier = (new IdentifierMetadata(stdClass::class, 'Id'))
            ->setGetter('getId');

        $metadata = (new ResourceObjectMetadata(\get_class($resource), 'Resource'))
            ->setIdentifier($identifier)
            ->setPrefix('/custom/resource');

        $schema = new MetadataSchema(new Factory(), $metadata);

        static::assertEquals('/custom/resource/aaa', $schema->getSelfLink($resource)->getStringRepresentation(''));
    }

    public function testGetAttributes(): void
    {
        $resource = new class() {
            public function getAttribute(): string
            {
                return 'aaa';
            }
        };

        $attribute = (new AttributeMetadata(stdClass::class, 'attribute'))
            ->setGetter('getAttribute');

        $metadata = (new ResourceObjectMetadata(\get_class($resource), 'Resource'))
            ->addAttribute($attribute);

        /** @var ContextInterface $context */
        $context = $this->getMockBuilder(ContextInterface::class)->disableOriginalConstructor()->getMock();
        $schema = new MetadataSchema($this->factory, $metadata);

        static::assertArrayHasKey('attribute', $schema->getAttributes($resource, $context));
        static::assertInstanceOf(Closure::class, $schema->getAttributes($resource, $context)['attribute']);
        static::assertEquals('aaa', $schema->getAttributes($resource, $context)['attribute']());
    }

    public function testNoRelationship(): void
    {
        $metadata = new ResourceObjectMetadata(stdClass::class, 'Resource');

        /** @var ContextInterface $context */
        $context = $this->getMockBuilder(ContextInterface::class)->disableOriginalConstructor()->getMock();
        $schema = new MetadataSchema($this->factory, $metadata);

        static::assertEmpty($schema->getRelationships(new stdClass(), $context));
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

        $relationshipA = (new RelationshipMetadata(stdClass::class, 'relationshipA'))
            ->setGetter('getRelationshipA')
            ->setGroups(['test'])
            ->addLink(new LinkMetadata('me', '/me'))
            ->setMeta(['meta' => 'data']);
        $relationshipB = (new RelationshipMetadata(stdClass::class, 'relationshipB'))
            ->setGetter('getRelationshipB')
            ->setGroups(['none']);

        $metadata = (new ResourceObjectMetadata(\get_class($resource), 'Resource'))
            ->addRelationship($relationshipA)
            ->addRelationship($relationshipB)
            ->setGroup('test');

        /** @var ContextInterface $context */
        $context = $this->getMockBuilder(ContextInterface::class)->disableOriginalConstructor()->getMock();
        $schema = new MetadataSchema($this->factory, $metadata);

        $relationships = $schema->getRelationships($resource, $context);

        static::assertArrayHasKey('relationshipA', $relationships);
        static::assertInstanceOf(Closure::class, $relationships['relationshipA'][SchemaInterface::RELATIONSHIP_DATA]);
        static::assertEquals('aaa', $relationships['relationshipA'][SchemaInterface::RELATIONSHIP_DATA]());
        static::assertArrayHasKey(SchemaInterface::RELATIONSHIP_LINKS_SELF, $relationships['relationshipA']);
        static::assertArrayHasKey(SchemaInterface::RELATIONSHIP_LINKS_RELATED, $relationships['relationshipA']);
        static::assertArrayHasKey(SchemaInterface::RELATIONSHIP_LINKS, $relationships['relationshipA']);
        static::assertArrayHasKey(SchemaInterface::RELATIONSHIP_META, $relationships['relationshipA']);
        static::assertArrayNotHasKey('relationshipB', $relationships);
    }

    public function testGetLinks(): void
    {
        $resource = new class() {
            public function getId(): string
            {
                return 'aaa';
            }
        };

        $identifier = (new IdentifierMetadata(stdClass::class, 'Id'))
            ->setGetter('getId');

        $metadata = (new ResourceObjectMetadata(\get_class($resource), 'Resource'))
            ->setIdentifier($identifier)
            ->addLink(new LinkMetadata('https://example.com/me', 'me'))
            ->setLinkSelf(false)
            ->setLinkRelated(false);

        $schema = new MetadataSchema(new Factory(), $metadata);

        static::assertArrayNotHasKey(LinkInterface::SELF, $schema->getLinks($resource));
        static::assertArrayHasKey('me', $schema->getLinks($resource));
        static::assertFalse($schema->isAddSelfLinkInRelationshipByDefault('relationship'));
        static::assertFalse($schema->isAddRelatedLinkInRelationshipByDefault('relationship'));
    }

    public function testGetMeta(): void
    {
        $resource = new class() {
            public function getId(): string
            {
                return 'aaa';
            }
        };

        $identifier = (new IdentifierMetadata(stdClass::class, 'Id'))
            ->setGetter('getId')
            ->setMeta(['meta' => 'data']);

        $metadata = (new ResourceObjectMetadata(\get_class($resource), 'Resource'))
            ->setIdentifier($identifier)
            ->setMeta(['meta' => 'value']);

        $schema = new MetadataSchema(new Factory(), $metadata);

        static::assertTrue($schema->hasIdentifierMeta($resource));
        static::assertEquals(['meta' => 'data'], $schema->getIdentifierMeta($resource));
        static::assertTrue($schema->hasResourceMeta($resource));
        static::assertEquals(['meta' => 'value'], $schema->getResourceMeta($resource));
    }
}
