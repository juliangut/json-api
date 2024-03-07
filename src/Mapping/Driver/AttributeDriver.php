<?php

/*
 * (c) 2018-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Mapping\Driver;

use Jgut\JsonApi\Mapping\Attribute\Attribute;
use Jgut\JsonApi\Mapping\Attribute\Identifier;
use Jgut\JsonApi\Mapping\Attribute\Link;
use Jgut\JsonApi\Mapping\Attribute\LinkRelated;
use Jgut\JsonApi\Mapping\Attribute\LinkSelf;
use Jgut\JsonApi\Mapping\Attribute\Meta;
use Jgut\JsonApi\Mapping\Attribute\Relationship;
use Jgut\JsonApi\Mapping\Attribute\ResourceObject;
use Jgut\JsonApi\Mapping\Metadata\AttributeMetadata;
use Jgut\JsonApi\Mapping\Metadata\IdentifierMetadata;
use Jgut\JsonApi\Mapping\Metadata\LinkMetadata;
use Jgut\JsonApi\Mapping\Metadata\RelationshipMetadata;
use Jgut\JsonApi\Mapping\Metadata\ResourceObjectMetadata;
use Jgut\Mapping\Driver\AbstractClassDriver;
use Jgut\Mapping\Exception\DriverException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class AttributeDriver extends AbstractClassDriver
{
    use PropertyTrait;

    /**
     * @return list<ResourceObjectMetadata>
     */
    public function getMetadata(): array
    {
        $resources = [];

        foreach ($this->getMappingClasses() as $class) {
            // @codeCoverageIgnoreStart
            if ($class->isAbstract()) {
                continue;
            }
            // @codeCoverageIgnoreEnd

            $resourceAttribute = $class->getAttributes(ResourceObject::class)[0] ?? null;
            if ($resourceAttribute !== null) {
                /** @var ResourceObject $resource */
                $resource = $resourceAttribute->newInstance();

                $resourceName = $resource->getName();
                if ($resourceName === null) {
                    $nameParts = explode('\\', $class->name);
                    /** @var string $name */
                    $name = end($nameParts);

                    $resourceName = lcfirst($name);
                }

                $resourceMetadata = new ResourceObjectMetadata($class->getName(), $resourceName);
                $this->populateResource($resourceMetadata, $class, $resource);

                $resources[$resourceName] = $resourceMetadata;
            }
        }

        return array_values($resources);
    }

    /**
     * @param ReflectionClass<object> $class
     *
     * @throws DriverException
     */
    protected function populateResource(
        ResourceObjectMetadata $resource,
        ReflectionClass $class,
        ResourceObject $resourceAttribute,
    ): void {
        foreach ($class->getProperties() as $property) {
            // @codeCoverageIgnoreStart
            if ($property->getDeclaringClass()->name !== $class->name) {
                continue;
            }
            // @codeCoverageIgnoreEnd

            $identifierAttribute = $property->getAttributes(Identifier::class)[0] ?? null;
            if ($identifierAttribute !== null) {
                /** @var Identifier $identifier */
                $identifier = $identifierAttribute->newInstance();

                $this->setResourceIdentifier($resource, $class, $property, $identifier);

                continue;
            }

            foreach ($property->getAttributes(Attribute::class) as $attributeAttribute) {
                /** @var Attribute $attribute */
                $attribute = $attributeAttribute->newInstance();

                $this->addResourceAttribute($resource, $class, $property, $attribute);
            }

            foreach ($property->getAttributes(Relationship::class) as $relationshipAttr) {
                /** @var Relationship $relationship */
                $relationship = $relationshipAttr->newInstance();

                $this->addResourceRelationship($resource, $class, $property, $relationship);
            }
        }

        if (!$resource->hasIdentifier()) {
            throw new DriverException(
                sprintf('Resource "%s" does not define an identifier.', $resource->getName()),
            );
        }

        $this->populateSchema($resource, $resourceAttribute);
        $this->populatePrefix($resource, $resourceAttribute);
        $this->populateLinks($resource, $class);
        $this->populateMeta($resource, $class);
    }

    /**
     * @param ReflectionClass<object> $class
     *
     * @throws DriverException
     */
    protected function setResourceIdentifier(
        ResourceObjectMetadata $resource,
        ReflectionClass $class,
        ReflectionProperty $property,
        Identifier $identifier,
    ): void {
        if ($resource->hasIdentifier()) {
            throw new DriverException(sprintf(
                'Resource "%s" cannot define more than one identifier.',
                $resource->getName(),
            ));
        }

        $identifierMetadata = new IdentifierMetadata(
            $property->getDeclaringClass()
                ->getName(),
            $identifier->getName() ?? $property->getName(),
        );

        $this->populateGetter($identifierMetadata, $class, $property, $identifier);
        $this->populateSetter($identifierMetadata, $class, $property, $identifier);
        $this->populateMeta($identifierMetadata, $property);

        $resource->setIdentifier($identifierMetadata);
    }

    /**
     * @param ReflectionClass<object> $class
     */
    protected function addResourceAttribute(
        ResourceObjectMetadata $resource,
        ReflectionClass $class,
        ReflectionProperty $property,
        Attribute $attribute,
    ): void {
        $attributeMetadata = new AttributeMetadata(
            $property->getDeclaringClass()
                ->getName(),
            $attribute->getName() ?? $property->getName(),
            $attribute->getGroups(),
        );

        $this->populateGetter($attributeMetadata, $class, $property, $attribute);
        $this->populateSetter($attributeMetadata, $class, $property, $attribute);

        $resource->addAttribute($attributeMetadata);
    }

    /**
     * @param ReflectionClass<object> $class
     */
    protected function addResourceRelationship(
        ResourceObjectMetadata $resource,
        ReflectionClass $class,
        ReflectionProperty $property,
        Relationship $relationship,
    ): void {
        $relationshipMetadata = new RelationshipMetadata(
            $property->getDeclaringClass()
                ->getName(),
            $relationship->getName() ?? $property->getName(),
            $relationship->getGroups(),
        );

        $this->populateGetter($relationshipMetadata, $class, $property, $relationship);
        $this->populateSetter($relationshipMetadata, $class, $property, $relationship);
        $this->populateLinks($relationshipMetadata, $property);
        $this->populateMeta($relationshipMetadata, $property);

        $resource->addRelationship($relationshipMetadata);
    }

    protected function populateSchema(ResourceObjectMetadata $resource, ResourceObject $attribute): void
    {
        $schema = $attribute->getSchema();
        if ($schema !== null) {
            $resource->setSchema($schema);
        }
    }

    protected function populatePrefix(ResourceObjectMetadata $resource, ResourceObject $attribute): void
    {
        $prefix = $attribute->getPrefix();
        if ($prefix !== null) {
            $resource->setPrefix($prefix);
        }
    }

    /**
     * @param ReflectionClass<object> $class
     *
     * @throws DriverException
     */
    protected function populateGetter(
        IdentifierMetadata|AttributeMetadata|RelationshipMetadata $metadata,
        ReflectionClass $class,
        ReflectionProperty $property,
        Identifier|Attribute|Relationship $attribute,
    ): void {
        $method = $this->getDefaultGetterMethod($property);

        if ($attribute->getGetter() !== null) {
            $method = $attribute->getGetter();

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
     * @param ReflectionClass<object> $class
     *
     * @throws DriverException
     */
    protected function populateSetter(
        IdentifierMetadata|AttributeMetadata|RelationshipMetadata $metadata,
        ReflectionClass $class,
        ReflectionProperty $property,
        Identifier|Attribute|Relationship $attribute,
    ): void {
        $method = $this->getDefaultSetterMethod($property);

        if ($attribute->getSetter() !== null) {
            $method = $attribute->getSetter();

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
     * @param ReflectionClass<object>|ReflectionProperty $reflector
     */
    protected function populateLinks(
        ResourceObjectMetadata|RelationshipMetadata $metadata,
        ReflectionClass|ReflectionProperty $reflector,
    ): void {
        $linkSelfAttribute = $reflector->getAttributes(LinkSelf::class)[0] ?? null;
        if ($linkSelfAttribute !== null) {
            /** @var LinkSelf $link */
            $link = $linkSelfAttribute->newInstance();

            $metadata->setLinkSelf($link->isIncluded());
        }

        $linkRelatedAttribute = $reflector->getAttributes(LinkRelated::class)[0] ?? null;
        if ($linkRelatedAttribute !== null) {
            /** @var LinkRelated $link */
            $link = $linkRelatedAttribute->newInstance();

            $metadata->setLinkRelated($link->isIncluded());
        }

        foreach ($reflector->getAttributes(Link::class) as $linkAttribute) {
            /** @var Link $link */
            $link = $linkAttribute->newInstance();

            $linkMetadata = new LinkMetadata($link->getHref(), $link->getTitle());
            $linkMetadata->setMeta($link->getMeta());

            $metadata->addLink($linkMetadata);
        }
    }

    /**
     * @param ReflectionClass<object>|ReflectionProperty $reflector
     */
    protected function populateMeta(
        ResourceObjectMetadata|IdentifierMetadata|RelationshipMetadata $metadata,
        ReflectionClass|ReflectionProperty $reflector,
    ): void {
        $metaList = [];

        foreach ($reflector->getAttributes(Meta::class) as $metaAttribute) {
            /** @var Meta $meta */
            $meta = $metaAttribute->newInstance();

            $metaList[$meta->getKey()] = $meta->getValue();
        }

        if (\count($metaList) !== 0) {
            $metadata->setMeta($metaList);
        }
    }
}
