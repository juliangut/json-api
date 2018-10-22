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
use Jgut\JsonApi\Mapping\Annotation\Id as IdAnnotation;
use Jgut\JsonApi\Mapping\Annotation\Relationship as RelationshipAnnotation;
use Jgut\JsonApi\Mapping\Annotation\Resource as ResourceAnnotation;
use Jgut\JsonApi\Mapping\Metadata\AttributeMetadata;
use Jgut\JsonApi\Mapping\Metadata\RelationshipMetadata;
use Jgut\JsonApi\Mapping\Metadata\ResourceMetadata;
use Jgut\Mapping\Driver\AbstractAnnotationDriver;
use Jgut\Mapping\Exception\DriverException;

/**
 * Annotation driver.
 */
class AnnotationDriver extends AbstractAnnotationDriver implements DriverInterface
{
    /**
     * {@inheritdoc}
     *
     * @return ResourceMetadata[]
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

            /** @var ResourceAnnotation|null $resource */
            $resource = $this->annotationReader->getClassAnnotation($class, ResourceAnnotation::class);
            if ($resource !== null) {
                $resourceName = $resource->getName();
                if ($resourceName === null) {
                    $nameParts = \explode('\\', $class->name);
                    $resourceName = \lcfirst(\end($nameParts));
                }

                $resources[$resourceName] = $this->getResourceMetadata($resourceName, $class, $resource);
            }
        }

        return $resources;
    }

    /**
     * Get resource metadata.
     *
     * @param string             $resourceName
     * @param \ReflectionClass   $class
     * @param ResourceAnnotation $annotation
     *
     * @throws DriverException
     *
     * @return ResourceMetadata
     */
    protected function getResourceMetadata(
        string $resourceName,
        \ReflectionClass $class,
        ResourceAnnotation $annotation
    ): ResourceMetadata {
        $resource = new ResourceMetadata($class->getName(), $resourceName);

        foreach ($class->getProperties() as $property) {
            // @codeCoverageIgnoreStart
            if ($property->getDeclaringClass()->name !== $class->name) {
                continue;
            }
            // @codeCoverageIgnoreEnd

            foreach ($this->annotationReader->getPropertyAnnotations($property) as $attributeAnnotation) {
                if ($attributeAnnotation instanceof IdAnnotation) {
                    $resource->setIdentifier($this->getAttributeMetadata($property, $attributeAnnotation));
                } elseif ($attributeAnnotation instanceof RelationshipAnnotation) {
                    $resource->addRelationship($this->getRelationshipMetadata($property, $attributeAnnotation));
                } elseif ($attributeAnnotation instanceof AttributeAnnotation) {
                    $resource->addAttribute($this->getAttributeMetadata($property, $attributeAnnotation));
                }
            }
        }

        if ($resource->getIdentifier() === null) {
            throw new DriverException(
                \sprintf('Resource "%s" does not define an id attribute', $resourceName)
            );
        }

        $this->populateResourceMetadata($resource, $annotation);

        return $resource;
    }

    /**
     * Populate resource metadata.
     *
     * @param ResourceMetadata   $resourceMetadata
     * @param ResourceAnnotation $resourceAnnotation
     */
    protected function populateResourceMetadata(
        ResourceMetadata $resourceMetadata,
        ResourceAnnotation $resourceAnnotation
    ): void {
        $schemaClass = $resourceAnnotation->getSchemaClass();
        if ($schemaClass !== null) {
            $resourceMetadata->setSchemaClass($schemaClass);
        }

        $url = $resourceAnnotation->getUrlPrefix();
        if ($url !== null) {
            $resourceMetadata->setUrlPrefix($url);
        }

        $resourceMetadata->setLinks($resourceAnnotation->getLinks());

        $resourceMetadata->setAttributesInInclude($resourceAnnotation->hasAttributesInInclude());
    }

    /**
     * Get relationship attribute metadata.
     *
     * @param \ReflectionProperty    $property
     * @param RelationshipAnnotation $annotation
     *
     * @return RelationshipMetadata
     */
    protected function getRelationshipMetadata(
        \ReflectionProperty $property,
        RelationshipAnnotation $annotation
    ): RelationshipMetadata {
        $relationship = new RelationshipMetadata(
            $property->getDeclaringClass()->getName(),
            $annotation->getName() ?? $property->getName()
        );

        $this->populateAttributeMetadata($relationship, $property, $annotation);

        return $relationship->setDefaultIncluded($annotation->isIncluded())
            ->setSelfLinkIncluded($annotation->isSelfLinkIncluded())
            ->setRelatedLinkIncluded($annotation->isRelatedLinkIncluded())
            ->setLinks($annotation->getLinks());
    }

    /**
     * Get attribute metadata.
     *
     * @param \ReflectionProperty $property
     * @param AttributeAnnotation $annotation
     *
     * @return AttributeMetadata
     */
    protected function getAttributeMetadata(
        \ReflectionProperty $property,
        AttributeAnnotation $annotation
    ): AttributeMetadata {
        $attribute = new AttributeMetadata(
            $property->getDeclaringClass()->getName(),
            $annotation->getName() ?? $property->getName()
        );

        $this->populateAttributeMetadata($attribute, $property, $annotation);

        return $attribute;
    }

    /**
     * Populate attribute metadata.
     *
     * @param AttributeMetadata   $attributeMetadata
     * @param \ReflectionProperty $property
     * @param AttributeAnnotation $attributeAnnotation
     */
    protected function populateAttributeMetadata(
        AttributeMetadata $attributeMetadata,
        \ReflectionProperty $property,
        AttributeAnnotation $attributeAnnotation
    ): void {
        $getter = $attributeAnnotation->getGetter();
        if ($getter === null) {
            $getterPrefix = 'get';

            $docComment = $property->getDeclaringClass()->getProperty($property->getName())->getDocComment();
            if (\is_string($docComment)
                && \preg_match('/@var\s+([a-zA-Z]+)(\s|\n)/', $docComment, $matches) === 1
                && \in_array('bool', \explode('|', $matches[1]), true)
            ) {
                $getterPrefix = 'is';
            }

            $getter = $getterPrefix . \ucfirst($attributeMetadata->getName());
        }

        $setter = $attributeAnnotation->getSetter();
        if ($setter === null) {
            $setter = 'set' . \ucfirst($attributeMetadata->getName());
        }

        $attributeMetadata
            ->setGetter($getter)
            ->setSetter($setter)
            ->setGroups($attributeAnnotation->getGroups());
    }
}
