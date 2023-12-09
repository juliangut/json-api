<?php

/*
 * (c) 2018-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Tests\Mapping\Driver;

use Jgut\JsonApi\Mapping\Driver\FileMappingTrait;
use Jgut\JsonApi\Mapping\Driver\JsonDriver;
use Jgut\JsonApi\Mapping\Driver\PhpDriver;
use Jgut\JsonApi\Mapping\Driver\XmlDriver;
use Jgut\JsonApi\Mapping\Driver\YamlDriver;
use Jgut\JsonApi\Tests\Mapping\Files\Classes\Valid\Annotation\ResourceOne as AnnotationResourceOne;
use Jgut\JsonApi\Tests\Mapping\Files\Classes\Valid\Attribute\ResourceOne as AttributeResourceOne;
use Jgut\Mapping\Exception\DriverException;

/**
 * @internal
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class FileMappingTraitTest extends AbstractDriverTestCase
{
    public function testNoClass(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('Resource class missing.');

        $driver = $this->getMockForTrait(FileMappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->willReturn([
                [],
            ]);

        $driver->getMetadata();
    }

    public function testClassNotFound(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('Resource class "unknownClass" not found.');

        $driver = $this->getMockForTrait(FileMappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->willReturn([
                [
                    'class' => 'unknownClass',
                ],
            ]);

        $driver->getMetadata();
    }

    public function testNoIdentifier(): void
    {
        $mappingClass = \PHP_VERSION_ID < 80_000
            ? AnnotationResourceOne::class
            : AttributeResourceOne::class;

        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('Resource does not define an identifier.');

        $driver = $this->getMockForTrait(FileMappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->willReturn([
                [
                    'class' => $mappingClass,
                ],
            ]);

        $driver->getMetadata();
    }

    public function testNoIdentifierProperty(): void
    {
        $mappingClass = \PHP_VERSION_ID < 80_000
            ? AnnotationResourceOne::class
            : AttributeResourceOne::class;

        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('Resource identifier property "id" does not exist.');

        $driver = $this->getMockForTrait(FileMappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->willReturn([
                [
                    'class' => $mappingClass,
                    'identifier' => 'id',
                ],
            ]);

        $driver->getMetadata();
    }

    public function testMissingAttributeProperty(): void
    {
        $mappingClass = \PHP_VERSION_ID < 80_000
            ? AnnotationResourceOne::class
            : AttributeResourceOne::class;

        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('Resource attribute property missing.');

        $driver = $this->getMockForTrait(FileMappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->willReturn([
                [
                    'class' => $mappingClass,
                    'identifier' => 'uuid',
                    'attributes' => [[]],
                ],
            ]);

        $driver->getMetadata();
    }

    public function testNoAttributeProperty(): void
    {
        $mappingClass = \PHP_VERSION_ID < 80_000
            ? AnnotationResourceOne::class
            : AttributeResourceOne::class;

        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('Resource attribute property "unknown" does not exist.');

        $driver = $this->getMockForTrait(FileMappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->willReturn([
                [
                    'class' => $mappingClass,
                    'identifier' => 'uuid',
                    'attributes' => [
                        [
                            'property' => 'unknown',
                        ],
                    ],
                ],
            ]);

        $driver->getMetadata();
    }

    public function testMissingRelationshipProperty(): void
    {
        $mappingClass = \PHP_VERSION_ID < 80_000
            ? AnnotationResourceOne::class
            : AttributeResourceOne::class;

        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('Resource relationship property missing.');

        $driver = $this->getMockForTrait(FileMappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->willReturn([
                [
                    'class' => $mappingClass,
                    'identifier' => 'uuid',
                    'relationships' => [[]],
                ],
            ]);

        $driver->getMetadata();
    }

    public function testNoRelationshipClass(): void
    {
        $mappingClass = \PHP_VERSION_ID < 80_000
            ? AnnotationResourceOne::class
            : AttributeResourceOne::class;

        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('Resource relationship property "unknown" does not exist.');

        $driver = $this->getMockForTrait(FileMappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->willReturn([
                [
                    'class' => $mappingClass,
                    'identifier' => 'uuid',
                    'relationships' => [
                        [
                            'property' => 'unknown',
                        ],
                    ],
                ],
            ]);

        $driver->getMetadata();
    }

    public function testMissingRelationshipClass(): void
    {
        $mappingClass = \PHP_VERSION_ID < 80_000
            ? AnnotationResourceOne::class
            : AttributeResourceOne::class;

        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('Resource relationship class missing.');

        $driver = $this->getMockForTrait(FileMappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->willReturn([
                [
                    'class' => $mappingClass,
                    'identifier' => 'uuid',
                    'relationships' => [
                        [
                            'property' => 'relative',
                        ],
                    ],
                ],
            ]);

        $driver->getMetadata();
    }

    public function testInvalidSchemaClass(): void
    {
        $mappingClass = \PHP_VERSION_ID < 80_000
            ? AnnotationResourceOne::class
            : AttributeResourceOne::class;

        $this->expectException(DriverException::class);
        $this->expectExceptionMessageMatches('/^Schema class ".+" does not implement ".+"\.$/');

        $driver = $this->getMockForTrait(FileMappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->willReturn([
                [
                    'class' => $mappingClass,
                    'identifier' => 'uuid',
                    'schema' => self::class,
                ],
            ]);

        $driver->getMetadata();
    }

    public function testPhpResources(): void
    {
        $driver = new PhpDriver([
            __DIR__ . '/../Files/Files/Valid/Php/ResourceOne.php',
            __DIR__ . '/../Files/Files/Valid/Php/ResourceTwo.php',
        ]);

        $this->checkResources($driver);
    }

    public function testJsonResources(): void
    {
        $driver = new JsonDriver([
            __DIR__ . '/../Files/Files/Valid/Json/ResourceOne.json',
            __DIR__ . '/../Files/Files/Valid/Json/ResourceTwo.json',
        ]);

        $this->checkResources($driver);
    }

    public function testXmlResources(): void
    {
        $driver = new XmlDriver([
            __DIR__ . '/../Files/Files/Valid/Xml/ResourceOne.xml',
            __DIR__ . '/../Files/Files/Valid/Xml/ResourceTwo.xml',
        ]);

        $this->checkResources($driver);
    }

    public function testYamlResources(): void
    {
        $driver = new YamlDriver([
            __DIR__ . '/../Files/Files/Valid/Yaml/ResourceOne.yaml',
            __DIR__ . '/../Files/Files/Valid/Yaml/ResourceTwo.yaml',
        ]);

        $this->checkResources($driver);
    }
}
