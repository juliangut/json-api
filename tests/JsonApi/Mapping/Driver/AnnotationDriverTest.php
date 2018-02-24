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

namespace Jgut\Slim\Routing\Tests\Mapping\Driver;

use Doctrine\Common\Annotations\AnnotationReader;
use Jgut\JsonApi\Mapping\Driver\AnnotationDriver;
use Jgut\JsonApi\Mapping\Metadata\AttributeMetadata;
use Jgut\JsonApi\Mapping\Metadata\ResourceMetadata;
use Jgut\JsonApi\Tests\Files\Annotation\Valid\ResourceOne;
use Jgut\JsonApi\Tests\Files\Annotation\Valid\ResourceTwo;
use PHPUnit\Framework\TestCase;

/**
 * Annotation mapping driver factory tests.
 */
class AnnotationDriverTest extends TestCase
{
    /**
     * @var AnnotationReader
     */
    protected $reader;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->reader = new AnnotationReader();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /^Resource ".+" does not define an id attribute$/
     */
    public function testNoIdResource()
    {
        $paths = [
            \dirname(__DIR__, 2) . '/Files/Annotation/Invalid/NoIdResource.php',
        ];

        $driver = new AnnotationDriver($paths, $this->reader);

        $driver->getMetadata();
    }

    public function testResources()
    {
        $paths = [
            \dirname(__DIR__, 2) . '/Files/Annotation/Valid/ResourceOne.php',
            \dirname(__DIR__, 2) . '/Files/Annotation/Valid/ResourceTwo.php',
        ];

        $driver = new AnnotationDriver($paths, $this->reader);

        /* @var ResourceMetadata[] $resources */
        $resources = $driver->getMetadata();

        $resource = $resources['resourceA'];
        self::assertInstanceOf(ResourceMetadata::class, $resource);
        self::assertEquals(ResourceOne::class, $resource->getClass());
        self::assertEquals('resourceA', $resource->getName());
        self::assertNull($resource->getUrl());
        self::assertNull($resource->getSchemaClass());
        self::assertFalse($resource->hasAttributesInInclude());
        self::assertInstanceOf(AttributeMetadata::class, $resource->getIdentifier());
        self::assertEquals(ResourceOne::class, $resource->getIdentifier()->getClass());
        self::assertEquals('id', $resource->getIdentifier()->getName());
        self::assertEquals('getId', $resource->getIdentifier()->getGetter());
        self::assertEquals('setId', $resource->getIdentifier()->getSetter());
        self::assertEquals(['default'], $resource->getIdentifier()->getGroups());

        $resource = $resources['resourceB'];
        self::assertInstanceOf(ResourceMetadata::class, $resource);
        self::assertEquals(ResourceTwo::class, $resource->getClass());
        self::assertEquals('resourceB', $resource->getName());
        self::assertEquals('/resource', $resource->getUrl());
        self::assertEquals('\Jgut\JsonApi\Test\Stubs\Schema', $resource->getSchemaClass());
        self::assertTrue($resource->hasAttributesInInclude());
        self::assertInstanceOf(AttributeMetadata::class, $resource->getIdentifier());
        self::assertEquals(ResourceTwo::class, $resource->getIdentifier()->getClass());
        self::assertEquals('uuid', $resource->getIdentifier()->getName());
        self::assertEquals('getUuid', $resource->getIdentifier()->getGetter());
        self::assertEquals('setUuid', $resource->getIdentifier()->getSetter());
        self::assertEquals(['default'], $resource->getIdentifier()->getGroups());
    }
}
