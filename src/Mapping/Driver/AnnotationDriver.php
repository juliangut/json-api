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
use Jgut\JsonApi\Mapping\Metadata\IdentifierMetadata;
use Jgut\JsonApi\Mapping\Metadata\LinkMetadata;
use Jgut\JsonApi\Mapping\Metadata\RelationshipMetadata;
use Jgut\JsonApi\Mapping\Metadata\ResourceMetadata;
use Jgut\Mapping\Driver\AbstractAnnotationDriver;
use Jgut\Mapping\Exception\DriverException;

/**
 * Annotation driver.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @param ResourceAnnotation $annotationAnnotation
     *
     * @throws DriverException
     *
     * @return ResourceMetadata
     */
    protected function getResourceMetadata(
        string $resourceName,
        \ReflectionClass $class,
        ResourceAnnotation $annotationAnnotation
    ): ResourceMetadata {
        $resourceMetadata = new ResourceMetadata($class->getName(), $resourceName);

        foreach ($class->getProperties() as $property) {
            // @codeCoverageIgnoreStart
            if ($property->getDeclaringClass()->name !== $class->name) {
                continue;
            }
            // @codeCoverageIgnoreEnd

            foreach ($this->annotationReader->getPropertyAnnotations($property) as $attributeAnnotation) {
                if ($attributeAnnotation instanceof IdAnnotation) {
                    $resourceMetadata->setIdentifier($this->getIdentifierMetadata($property, $attributeAnnotation));
                } elseif ($attributeAnnotation instanceof RelationshipAnnotation) {
                    $resourceMetadata->addRelationship($this->getRelationshipMetadata($property, $attributeAnnotation));
                } elseif ($attributeAnnotation instanceof AttributeAnnotation) {
                    $resourceMetadata->addAttribute($this->getAttributeMetadata($property, $attributeAnnotation));
                }
            }
        }

        $resourceMetadata->getIdentifier();

        $this->populateResource($resourceMetadata, $annotationAnnotation);

        return $resourceMetadata;
    }

    /**
     * Populate resource.
     *
     * @param ResourceMetadata   $resourceMetadata
     * @param ResourceAnnotation $resourceAnnotation
     */
    protected function populateResource(
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

        $selfLinkIncluded = $resourceAnnotation->isSelfLinkIncluded();
        if ($selfLinkIncluded !== null) {
            $resourceMetadata->setSelfLinkIncluded($selfLinkIncluded);
        }
        $relatedLinkIncluded = $resourceAnnotation->isRelatedLinkIncluded();
        if ($relatedLinkIncluded !== null) {
            $resourceMetadata->setRelatedLinkIncluded($relatedLinkIncluded);
        }

        foreach ($this->getLinks($resourceAnnotation->getLinks()) as $link) {
            $resourceMetadata->addLink($link);
        }

        $resourceMetadata->setMeta($this->getMeta($resourceAnnotation->getMeta()));
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
        $relationshipMetadata = new RelationshipMetadata(
            $property->getDeclaringClass()->getName(),
            $annotation->getName() ?? $property->getName()
        );

        $this->populateAttributeMetadata($relationshipMetadata, $property, $annotation);

        $selfLinkIncluded = $annotation->isSelfLinkIncluded();
        if ($selfLinkIncluded !== null) {
            $relationshipMetadata->setSelfLinkIncluded($selfLinkIncluded);
        }
        $relatedLinkIncluded = $annotation->isRelatedLinkIncluded();
        if ($relatedLinkIncluded !== null) {
            $relationshipMetadata->setRelatedLinkIncluded($relatedLinkIncluded);
        }

        foreach ($this->getLinks($annotation->getLinks()) as $link) {
            $relationshipMetadata->addLink($link);
        }

        $relationshipMetadata->setMeta($this->getMeta($annotation->getMeta()));

        return $relationshipMetadata;
    }

    /**
     * Get id attribute metadata.
     *
     * @param \ReflectionProperty $property
     * @param AttributeAnnotation $annotation
     *
     * @return IdentifierMetadata
     */
    protected function getIdentifierMetadata(
        \ReflectionProperty $property,
        AttributeAnnotation $annotation
    ): IdentifierMetadata {
        $name = $annotation->getName() ?? $property->getName();

        $identifier = new IdentifierMetadata($property->getDeclaringClass()->getName(), $name);

        $getter = $annotation->getGetter();
        if ($getter === null) {
            $getter = 'get' . \ucfirst($name);
        }

        $identifier->setGetter($getter);

        return $identifier;
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

    /**
     * Get links.
     *
     * @param array<mixed, string> $links
     *
     * @return array<mixed, LinkMetadata>
     */
    protected function getLinks(array $links): array
    {
        if ($links !== [] && \array_keys($links) === \range(0, \count($links) - 1)) {
            throw new DriverException('Links keys must be all strings');
        }

        $linkList = [];
        foreach ($links as $name => $href) {
            $linkList[$name] = new LinkMetadata($name, $href);
        }

        return $linkList;
    }

    /**
     * Get meta data.
     *
     * @param array<mixed, string> $meta
     *
     * @return array<string, mixed>
     */
    protected function getMeta(array $meta): array
    {
        if ($meta !== [] && \array_keys($meta) === \range(0, \count($meta) - 1)) {
            throw new DriverException('Metadata keys must be all strings');
        }

        return $meta;
    }
}
