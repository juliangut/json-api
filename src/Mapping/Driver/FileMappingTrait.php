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

namespace Jgut\JsonApi\Mapping\Driver;

use Jgut\JsonApi\Mapping\Metadata\AttributeMetadata;
use Jgut\JsonApi\Mapping\Metadata\IdentifierMetadata;
use Jgut\JsonApi\Mapping\Metadata\RelationshipMetadata;
use Jgut\JsonApi\Mapping\Metadata\ResourceObjectMetadata;
use Jgut\JsonApi\Schema\MetadataSchemaInterface;
use Jgut\Mapping\Exception\DriverException;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

trait FileMappingTrait
{
    use LinksTrait;
    use PropertyTrait;

    /**
     * @return list<ResourceObjectMetadata>
     */
    public function getMetadata(): array
    {
        /** @var array<ResourceMapping|mixed> $mappingData */
        $mappingData = $this->getMappingData();

        return $this->getResourcesMetadata($mappingData);
    }

    /**
     * @return array<ResourceMapping>
     */
    abstract protected function getMappingData(): array;

    /**
     * @param array<ResourceMapping|mixed> $mappingData
     *
     * @throws DriverException
     *
     * @return list<ResourceObjectMetadata>
     */
    protected function getResourcesMetadata(array $mappingData): array
    {
        $resources = [];

        foreach ($mappingData as $mapping) {
            if (!\is_array($mapping)) {
                continue;
            }

            /** @var string|null $resourceClass */
            $resourceClass = $mapping['class'] ?? null;

            if ($resourceClass === null) {
                throw new DriverException('Resource class missing.');
            }

            if (!class_exists($resourceClass)) {
                throw new DriverException(sprintf('Resource class "%s" not found.', $resourceClass));
            }

            $class = new ReflectionClass($resourceClass);

            $resourceName = $this->getName($mapping, $class);
            $resource = new ResourceObjectMetadata($resourceClass, $resourceName);

            $resources[$resourceName] = $this->populateResource($resource, $class, $mapping);
        }

        return array_values($resources);
    }

    /**
     * @param ResourceMapping         $mapping
     * @param ReflectionClass<object> $class
     */
    protected function getName(array $mapping, ReflectionClass $class): string
    {
        if (\array_key_exists('name', $mapping)) {
            return $mapping['name'];
        }

        $nameParts = explode('\\', $class->getName());

        return lcfirst(end($nameParts));
    }

    /**
     * @param ReflectionClass<object> $class
     * @param ResourceMapping         $mapping
     *
     * @throws DriverException
     */
    protected function populateResource(
        ResourceObjectMetadata $resource,
        ReflectionClass $class,
        array $mapping,
    ): ResourceObjectMetadata {
        $this->setResourceIdentifier($resource, $class, $mapping);

        if (\array_key_exists('attributes', $mapping)) {
            foreach ($mapping['attributes'] as $attributeMapping) {
                $this->addResourceAttribute($resource, $class, $attributeMapping);
            }
        }
        if (\array_key_exists('relationships', $mapping)) {
            foreach ($mapping['relationships'] as $relationshipMapping) {
                $this->addResourceRelationship($resource, $class, $relationshipMapping);
            }
        }

        $this->populateSchema($resource, $mapping);
        $this->populatePrefix($resource, $mapping);
        $this->populateLinks($resource, $mapping);
        $this->populateMeta($resource, $mapping);

        return $resource;
    }

    /**
     * @param ReflectionClass<object>                      $class
     * @param array{identifier?: string|IdentifierMapping} $mapping
     *
     * @throws DriverException
     */
    protected function setResourceIdentifier(
        ResourceObjectMetadata $metadata,
        ReflectionClass $class,
        array $mapping,
    ): void {
        if (!\array_key_exists('identifier', $mapping)) {
            throw new DriverException('Resource does not define an identifier.');
        }

        $identifier = \is_string($mapping['identifier']) && trim($mapping['identifier']) !== ''
            ? trim($mapping['identifier'])
            : $mapping['identifier'];

        if (!\is_array($identifier)) {
            $identifier = ['property' => $identifier];
        }

        /** @var IdentifierMapping $identifier */
        $mapping['identifier'] = $identifier;

        /** @var string $property */
        $property = $identifier['property'];

        $properties = array_filter(
            $class->getProperties(),
            static fn(ReflectionProperty $reflectionProperty): bool => $reflectionProperty->getName() === $property,
        );
        if (\count($properties) !== 1) {
            throw new DriverException(sprintf('Resource identifier property "%s" does not exist.', $property));
        }

        /** @var string $name */
        $name = $mapping['identifier']['name'] ?? $property;

        $identifierMetadata = new IdentifierMetadata($metadata->getClass(), $name);

        $this->populateGetter($identifierMetadata, $class, $properties[0], $mapping['identifier']);
        $this->populateSetter($identifierMetadata, $class, $properties[0], $mapping['identifier']);
        $this->populateMeta($identifierMetadata, $mapping['identifier']);

        $metadata->setIdentifier($identifierMetadata);
    }

    /**
     * @param ReflectionClass<object> $class
     * @param AttributeMapping        $mapping
     *
     * @throws DriverException
     */
    protected function addResourceAttribute(
        ResourceObjectMetadata $metadata,
        ReflectionClass $class,
        array $mapping,
    ): void {
        if (!\array_key_exists('property', $mapping)) {
            throw new DriverException('Resource attribute property missing.');
        }

        /** @var string $property */
        $property = $mapping['property'];

        $properties = array_values(array_filter(
            $class->getProperties(),
            static fn(ReflectionProperty $reflectionProperty): bool => $reflectionProperty->getName() === $property,
        ));
        if (\count($properties) !== 1) {
            throw new DriverException(sprintf('Resource attribute property "%s" does not exist.', $property));
        }

        /** @var string $name */
        $name = $mapping['name'] ?? $property;

        $attributeMetadata = new AttributeMetadata($metadata->getClass(), $name, $this->getGroups($mapping));

        $this->populateGetter($attributeMetadata, $class, $properties[0], $mapping);
        $this->populateSetter($attributeMetadata, $class, $properties[0], $mapping);

        $metadata->addAttribute($attributeMetadata);
    }

    /**
     * @param ReflectionClass<object> $class
     * @param RelationshipMapping     $mapping
     *
     * @throws DriverException
     */
    protected function addResourceRelationship(
        ResourceObjectMetadata $metadata,
        ReflectionClass $class,
        array $mapping,
    ): void {
        if (!\array_key_exists('property', $mapping)) {
            throw new DriverException('Resource relationship property missing.');
        }

        /** @var string $property */
        $property = $mapping['property'];

        $properties = array_values(array_filter(
            $class->getProperties(),
            static fn(ReflectionProperty $reflectionProperty): bool => $reflectionProperty->getName() === $property,
        ));
        if (\count($properties) !== 1) {
            throw new DriverException(sprintf('Resource relationship property "%s" does not exist.', $property));
        }

        /** @var string $name */
        $name = $mapping['name'] ?? $property;

        if (!\array_key_exists('class', $mapping)) {
            throw new DriverException('Resource relationship class missing.');
        }

        $relationshipMetadata = new RelationshipMetadata($mapping['class'], $name, $this->getGroups($mapping));

        $this->populateGetter($relationshipMetadata, $class, $properties[0], $mapping);
        $this->populateSetter($relationshipMetadata, $class, $properties[0], $mapping);
        $this->populateLinks($relationshipMetadata, $mapping);
        $this->populateMeta($relationshipMetadata, $mapping);

        $metadata->addRelationship($relationshipMetadata);
    }

    /**
     * @param AttributeMapping|RelationshipMapping $mapping
     *
     * @throws DriverException
     *
     * @return array<string>
     */
    protected function getGroups(array $mapping): array
    {
        if (!\array_key_exists('groups', $mapping)) {
            return [];
        }

        $groups = $mapping['groups'];

        foreach ($groups as $group) {
            if (!\is_string($group) || $group === '') {
                throw new DriverException(sprintf(
                    'Groups must be a non empty string or an array of non empty strings. "%s" given.',
                    \gettype($group),
                ));
            }
        }

        /** @var array<string> $groups */
        return array_values($groups);
    }

    /**
     * @param ResourceMapping $mapping
     *
     * @throws DriverException
     */
    protected function populateSchema(ResourceObjectMetadata $metadata, array $mapping): void
    {
        if (\array_key_exists('schema', $mapping)) {
            $schema = $mapping['schema'];
            if (!class_exists($schema)) {
                throw new DriverException(sprintf('Schema class "%s" does not exist.', $schema));
            }

            $implements = class_implements($schema);
            if ($implements === false || !\in_array(MetadataSchemaInterface::class, $implements, true)) {
                throw new DriverException(
                    sprintf(
                        'Schema class "%s" does not implement "%s".',
                        $schema,
                        MetadataSchemaInterface::class,
                    ),
                );
            }

            /** @var class-string<SchemaInterface> $schema */
            $metadata->setSchema($schema);
        }
    }

    /**
     * @param ResourceMapping $mapping
     */
    protected function populatePrefix(ResourceObjectMetadata $metadata, array $mapping): void
    {
        if (\array_key_exists('prefix', $mapping)) {
            $metadata->setPrefix($mapping['prefix']);
        }
    }

    /**
     * @param IdentifierMetadata|AttributeMetadata|RelationshipMetadata $metadata
     * @param ReflectionClass<object>                                   $class
     * @param IdentifierMapping|AttributeMapping|RelationshipMapping    $mapping
     *
     * @throws DriverException
     */
    protected function populateGetter(
        $metadata,
        ReflectionClass $class,
        ReflectionProperty $property,
        array $mapping,
    ): void {
        $method = $this->getDefaultGetterMethod($property);

        if (\array_key_exists('getter', $mapping)) {
            /** @var string $method */
            $method = $mapping['getter'];

            $methods = array_filter(
                $class->getMethods(),
                static fn(ReflectionMethod $reflectionMethod): bool
                    => $reflectionMethod->getName() === $method && $reflectionMethod->isPublic(),
            );
            if (\count($methods) !== 1) {
                throw new DriverException(sprintf('Getter method "%s" does not exist or is not public.', $method));
            }
        }

        $metadata->setGetter($method);
    }

    /**
     * @param IdentifierMetadata|AttributeMetadata|RelationshipMetadata $metadata
     * @param ReflectionClass<object>                                   $class
     * @param IdentifierMapping|AttributeMapping|RelationshipMapping    $mapping
     *
     * @throws DriverException
     */
    protected function populateSetter(
        $metadata,
        ReflectionClass $class,
        ReflectionProperty $property,
        array $mapping,
    ): void {
        $method = $this->getDefaultSetterMethod($property);

        if (\array_key_exists('setter', $mapping)) {
            /** @var string $method */
            $method = $mapping['setter'];

            $methods = array_filter(
                $class->getMethods(),
                static fn(ReflectionMethod $reflectionMethod): bool
                    => $reflectionMethod->getName() === $method && $reflectionMethod->isPublic(),
            );
            if (\count($methods) !== 1) {
                throw new DriverException(sprintf('Setter method "%s" does not exist or is not public.', $method));
            }
        }

        $metadata->setSetter($method);
    }

    /**
     * @param ResourceObjectMetadata|RelationshipMetadata $metadata
     * @param ResourceMapping|RelationshipMapping         $mapping
     */
    protected function populateLinks($metadata, array $mapping): void
    {
        if (\array_key_exists('linkSelf', $mapping)) {
            $metadata->setLinkSelf((bool) $mapping['linkSelf']);
        }

        if (\array_key_exists('linkRelated', $mapping)) {
            $metadata->setLinkRelated((bool) $mapping['linkRelated']);
        }

        if (\array_key_exists('links', $mapping)) {
            foreach ($this->getLinksMetadata($mapping['links']) as $link) {
                $metadata->addLink($link);
            }
        }
    }

    /**
     * @param ResourceObjectMetadata|IdentifierMetadata|RelationshipMetadata $metadata
     * @param ResourceMapping|IdentifierMapping|RelationshipMapping          $mapping
     *
     * @throws DriverException
     */
    protected function populateMeta($metadata, array $mapping): void
    {
        if (!\array_key_exists('meta', $mapping)) {
            return;
        }

        $meta = $mapping['meta'];
        if (!\is_array($meta) || ($meta !== [] && array_keys($meta) === range(0, \count($meta) - 1))) {
            throw new DriverException('Metadata must be an array which keys are all strings.');
        }

        /** @var array<string, mixed> $meta */
        $metadata->setMeta($meta);
    }
}
