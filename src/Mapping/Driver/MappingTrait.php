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
use Jgut\JsonApi\Mapping\Metadata\RelationshipMetadata;
use Jgut\JsonApi\Mapping\Metadata\ResourceMetadata;
use Jgut\JsonApi\Schema\MetadataSchemaInterface;

/**
 * Mapping definition trait.
 */
trait MappingTrait
{
    /**
     * Get mapped metadata.
     *
     * @return ResourceMetadata[]
     */
    public function getMetadata(): array
    {
        return $this->getResourcesMetadata($this->getMappingData());
    }

    /**
     * Get mapping data.
     *
     * @return mixed[]
     */
    abstract protected function getMappingData(): array;

    /**
     * Get resources metadata.
     *
     * @param array $mappingData
     *
     * @throws \InvalidArgumentException
     *
     * @return ResourceMetadata[]
     */
    protected function getResourcesMetadata(array $mappingData): array
    {
        $resources = [];

        foreach ($mappingData as $mapping) {
            $resources[] = $this->getResourceMetadata($mapping);
        }

        return $resources;
    }

    /**
     * Get resource metadata.
     *
     * @param array $mapping
     *
     * @throws \InvalidArgumentException
     *
     * @return ResourceMetadata
     */
    protected function getResourceMetadata(array $mapping): ResourceMetadata
    {
        if (!isset($mapping['class'])) {
            throw new \InvalidArgumentException('Resource class missing');
        }

        if (!isset($mapping['name'])) {
            $nameParts = \explode('\\', $mapping['class']);
            $mapping['name'] = \lcfirst(\end($nameParts));
        }

        $resource = (new ResourceMetadata($mapping['class'], $mapping['name']))
            ->setIdentifier($this->getIdAttribute($mapping))
            ->setIncludeAttributes($this->isIncludeAttributes($mapping));

        $schemaClass = $this->getSchemaClass($mapping);
        if ($schemaClass !== null) {
            if (!\class_exists($schemaClass)
                || !\in_array(MetadataSchemaInterface::class, \class_implements($schemaClass))
            ) {
                throw new \InvalidArgumentException(
                    \sprintf(
                        'Schema class "%s" does not exist or does not implement "%s"',
                        $schemaClass,
                        MetadataSchemaInterface::class
                    )
                );
            }

            $resource->setSchemaClass($schemaClass);
        }

        $this->populateRelationships($resource, $mapping);
        $this->populateAttributes($resource, $mapping);

        return $resource;
    }

    /**
     * Set attributes visibility when being included.
     *
     * @param array $mapping
     *
     * @return bool
     */
    protected function isIncludeAttributes(array $mapping): bool
    {
        return isset($mapping['includeAttributes']) ? (bool) $mapping['includeAttributes'] : true;
    }

    /**
     * Get schema provider class.
     *
     * @param array $mapping
     *
     * @return string|null
     */
    protected function getSchemaClass(array $mapping): ?string
    {
        return $mapping['schemaClass'] ?? null;
    }

    /**
     * Get resource id attribute.
     *
     * @param array $mapping
     *
     * @throws \InvalidArgumentException
     *
     * @return AttributeMetadata
     */
    protected function getIdAttribute(array $mapping): AttributeMetadata
    {
        if (!isset($mapping['id'])) {
            $mapping['id'] = ['name' => 'id'];
        }

        return $this->getAttributeMetadata($mapping['class'], $mapping['id']);
    }

    /**
     * Populate resource relationships.
     *
     * @param ResourceMetadata $resourceMetadata
     * @param array            $mapping
     */
    protected function populateRelationships(ResourceMetadata $resourceMetadata, array $mapping): void
    {
        // @codeCoverageIgnoreStart
        if (!isset($mapping['relationships'])) {
            return;
        }
        // @codeCoverageIgnoreEnd

        foreach ($mapping['relationships'] as $relationshipMapping) {
            $resourceMetadata->addRelationship($this->getRelationshipMetadata($relationshipMapping));
        }
    }

    /**
     * Populate resource attributes.
     *
     * @param ResourceMetadata $resourceMetadata
     * @param array            $mapping
     */
    protected function populateAttributes(ResourceMetadata $resourceMetadata, array $mapping): void
    {
        // @codeCoverageIgnoreStart
        if (!isset($mapping['attributes'])) {
            return;
        }
        // @codeCoverageIgnoreEnd

        foreach ($mapping['attributes'] as $attributeMapping) {
            $resourceMetadata->addAttribute(
                $this->getAttributeMetadata($resourceMetadata->getClass(), $attributeMapping)
            );
        }
    }

    /**
     * Ger resource relationship metadata.
     *
     * @param array $mapping
     *
     * @return RelationshipMetadata
     */
    protected function getRelationshipMetadata(array $mapping): RelationshipMetadata
    {
        if (!isset($mapping['class'])) {
            throw new \InvalidArgumentException('Resource relationship class missing');
        }

        if (!isset($mapping['name'])) {
            $nameParts = \explode('\\', $mapping['class']);
            $mapping['name'] = \lcfirst(\end($nameParts));
        }

        $relationship = new RelationshipMetadata($mapping['class'], $mapping['name']);

        $this->populateAttribute($relationship, $mapping);

        return $relationship->setDefaultIncluded($this->isDefaultIncluded($mapping))
            ->setSelfLinkIncluded($this->isSelfLinkIncluded($mapping))
            ->setRelatedLinkIncluded($this->isRelatedLinkIncluded($mapping));
    }

    /**
     * Get resource attribute metadata.
     *
     * @param string $class
     * @param array  $mapping
     *
     * @return AttributeMetadata
     */
    protected function getAttributeMetadata(string $class, array $mapping): AttributeMetadata
    {
        $attribute = new AttributeMetadata($class, $mapping['name']);

        $this->populateAttribute($attribute, $mapping);

        return $attribute;
    }

    /**
     * Populate attribute.
     *
     * @param AttributeMetadata $attribute
     * @param array             $mapping
     */
    protected function populateAttribute(AttributeMetadata $attribute, array $mapping): void
    {
        $attribute
            ->setGetter($this->getGetter($mapping))
            ->setSetter($this->getSetter($mapping))
            ->setGroups($this->getGroups($mapping));
    }

    /**
     * Get attribute getter method.
     *
     * @param array $mapping
     *
     * @return string
     */
    protected function getGetter(array $mapping): string
    {
        return $mapping['getter'] ?? 'get' . \ucfirst($mapping['name']);
    }

    /**
     * Get attribute setter method.
     *
     * @param array $mapping
     *
     * @return string
     */
    protected function getSetter(array $mapping): string
    {
        return $mapping['setter'] ?? 'set' . \ucfirst($mapping['name']);
    }

    /**
     * Get attribute groups.
     *
     * @param array $mapping
     *
     * @return array
     */
    protected function getGroups(array $mapping): array
    {
        return $mapping['groups'] ?? ['default'];
    }

    /**
     * Get attribute default inclusion.
     *
     * @param array $mapping
     *
     * @return bool
     */
    protected function isDefaultIncluded(array $mapping): bool
    {
        return isset($mapping['included']) ? (bool) $mapping['included'] : false;
    }

    /**
     * Get relationship self link default inclusion.
     *
     * @param array $mapping
     *
     * @return bool
     */
    protected function isSelfLinkIncluded(array $mapping): bool
    {
        return isset($mapping['selfLinkIncluded']) ? (bool) $mapping['selfLinkIncluded'] : false;
    }

    /**
     * Get relationship related link default inclusion.
     *
     * @param array $mapping
     *
     * @return bool
     */
    protected function isRelatedLinkIncluded(array $mapping): bool
    {
        return isset($mapping['relatedLinkIncluded']) ? (bool) $mapping['relatedLinkIncluded'] : false;
    }
}
