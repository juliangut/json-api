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

use Jgut\JsonApi\Mapping\Annotation\Attribute as AttributeAnnotation;
use Jgut\JsonApi\Mapping\Annotation\Identifier as IdentifierAnnotation;
use Jgut\JsonApi\Mapping\Annotation\Relationship as RelationshipAnnotation;
use Jgut\JsonApi\Mapping\Annotation\ResourceObject as ResourceObjectAnnotation;
use Jgut\JsonApi\Mapping\Metadata\AttributeMetadata;
use Jgut\JsonApi\Mapping\Metadata\IdentifierMetadata;
use Jgut\JsonApi\Mapping\Metadata\RelationshipMetadata;
use Jgut\JsonApi\Mapping\Metadata\ResourceObjectMetadata;
use Jgut\Mapping\Driver\AbstractAnnotationDriver;
use Jgut\Mapping\Exception\DriverException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AnnotationDriver extends AbstractAnnotationDriver implements DriverInterface
{
    use PropertyTrait;
    use LinksTrait;

    /**
     * {@inheritDoc}
     *
     * @return array<ResourceObjectMetadata>
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

            /** @var ResourceObjectAnnotation|null $annotation */
            $annotation = $this->annotationReader->getClassAnnotation($class, ResourceObjectAnnotation::class);
            if ($annotation !== null) {
                $resourceName = $annotation->getName();
                if ($resourceName === null) {
                    $nameParts = explode('\\', $class->name);
                    /** @var string $name */
                    $name = end($nameParts);

                    $resourceName = lcfirst($name);
                }

                $resourceMetadata = new ResourceObjectMetadata($class->getName(), $resourceName);
                $this->populateResource($resourceMetadata, $class, $annotation);

                $resources[$resourceName] = $resourceMetadata;
            }
        }

        return $resources;
    }

    /**
     * @param ReflectionClass<object> $class
     *
     * @throws DriverException
     */
    protected function populateResource(
        ResourceObjectMetadata $resource,
        ReflectionClass $class,
        ResourceObjectAnnotation $annotation
    ): void {
        foreach ($class->getProperties() as $property) {
            // @codeCoverageIgnoreStart
            if ($property->getDeclaringClass()->name !== $class->name) {
                continue;
            }
            // @codeCoverageIgnoreEnd

            foreach ($this->annotationReader->getPropertyAnnotations($property) as $attributeAnnotation) {
                if ($attributeAnnotation instanceof IdentifierAnnotation) {
                    $this->setResourceIdentifier($resource, $class, $property, $attributeAnnotation);
                } elseif ($attributeAnnotation instanceof AttributeAnnotation) {
                    $this->addResourceAttribute($resource, $class, $property, $attributeAnnotation);
                } elseif ($attributeAnnotation instanceof RelationshipAnnotation) {
                    $this->addResourceRelationship($resource, $class, $property, $attributeAnnotation);
                }
            }
        }

        if (!$resource->hasIdentifier()) {
            throw new DriverException(
                sprintf('Resource "%s" does not define an identifier.', $resource->getName()),
            );
        }

        $this->populateSchema($resource, $annotation);
        $this->populatePrefix($resource, $annotation);
        $this->populateLinks($resource, $annotation);
        $this->populateMeta($resource, $annotation);
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
        IdentifierAnnotation $annotation
    ): void {
        if ($resource->hasIdentifier()) {
            throw new DriverException(sprintf(
                'Resource "%s" cannot define more than one identifier.',
                $resource->getName(),
            ));
        }

        $identifier = new IdentifierMetadata(
            $property->getDeclaringClass()
                ->getName(),
            $annotation->getName() ?? $property->getName(),
        );

        $this->populateGetter($identifier, $class, $property, $annotation);
        $this->populateSetter($identifier, $class, $property, $annotation);
        $this->populateMeta($identifier, $annotation);

        $resource->setIdentifier($identifier);
    }

    /**
     * @param ReflectionClass<object> $class
     */
    protected function addResourceAttribute(
        ResourceObjectMetadata $resource,
        ReflectionClass $class,
        ReflectionProperty $property,
        AttributeAnnotation $annotation
    ): void {
        $attribute = new AttributeMetadata(
            $property->getDeclaringClass()
                ->getName(),
            $annotation->getName() ?? $property->getName(),
            $annotation->getGroups(),
        );

        $this->populateGetter($attribute, $class, $property, $annotation);
        $this->populateSetter($attribute, $class, $property, $annotation);

        $resource->addAttribute($attribute);
    }

    /**
     * @param ReflectionClass<object> $class
     */
    protected function addResourceRelationship(
        ResourceObjectMetadata $resource,
        ReflectionClass $class,
        ReflectionProperty $property,
        RelationshipAnnotation $annotation
    ): void {
        $relationship = new RelationshipMetadata(
            $property->getDeclaringClass()
                ->getName(),
            $annotation->getName() ?? $property->getName(),
            $annotation->getGroups(),
        );

        $this->populateGetter($relationship, $class, $property, $annotation);
        $this->populateSetter($relationship, $class, $property, $annotation);
        $this->populateLinks($relationship, $annotation);
        $this->populateMeta($relationship, $annotation);

        $resource->addRelationship($relationship);
    }

    protected function populateSchema(ResourceObjectMetadata $resource, ResourceObjectAnnotation $annotation): void
    {
        $class = $annotation->getSchema();
        if ($class !== null) {
            $resource->setSchema($class);
        }
    }

    protected function populatePrefix(ResourceObjectMetadata $resource, ResourceObjectAnnotation $annotation): void
    {
        $prefix = $annotation->getPrefix();
        if ($prefix !== null) {
            $resource->setPrefix($prefix);
        }
    }

    /**
     * @param IdentifierMetadata|AttributeMetadata|RelationshipMetadata       $metadata
     * @param ReflectionClass<object>                                         $class
     * @param IdentifierAnnotation|AttributeAnnotation|RelationshipAnnotation $annotation
     *
     * @throws DriverException
     */
    protected function populateGetter(
        $metadata,
        ReflectionClass $class,
        ReflectionProperty $property,
        $annotation
    ): void {
        $method = $this->getDefaultGetterMethod($property);

        if ($annotation->getGetter() !== null) {
            $method = $annotation->getGetter();

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
     * @param IdentifierMetadata|AttributeMetadata|RelationshipMetadata       $metadata
     * @param ReflectionClass<object>                                         $class
     * @param IdentifierAnnotation|AttributeAnnotation|RelationshipAnnotation $annotation
     *
     * @throws DriverException
     */
    protected function populateSetter(
        $metadata,
        ReflectionClass $class,
        ReflectionProperty $property,
        $annotation
    ): void {
        $method = $this->getDefaultSetterMethod($property);

        if ($annotation->getSetter() !== null) {
            $method = $annotation->getSetter();

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
     * @param ResourceObjectMetadata|RelationshipMetadata     $metadata
     * @param ResourceObjectAnnotation|RelationshipAnnotation $annotation
     */
    protected function populateLinks($metadata, $annotation): void
    {
        $selfLinkIncluded = $annotation->isLinkSelf();
        if ($selfLinkIncluded !== null) {
            $metadata->setLinkSelf($selfLinkIncluded);
        }

        $relatedLinkIncluded = $annotation->isLinkRelated();
        if ($relatedLinkIncluded !== null) {
            $metadata->setLinkRelated($relatedLinkIncluded);
        }

        foreach ($this->getLinksMetadata($annotation->getLinks()) as $link) {
            $metadata->addLink($link);
        }
    }

    /**
     * @param ResourceObjectMetadata|IdentifierMetadata|RelationshipMetadata       $metadata
     * @param ResourceObjectAnnotation|IdentifierAnnotation|RelationshipAnnotation $annotation
     *
     * @throws DriverException
     */
    protected function populateMeta($metadata, $annotation): void
    {
        $metaList = $annotation->getMeta();
        if (\count($metaList) !== 0) {
            $metadata->setMeta($metaList);
        }
    }
}
