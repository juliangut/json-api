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
use Jgut\JsonApi\Mapping\Metadata\LinkMetadata;
use Jgut\JsonApi\Mapping\Metadata\RelationshipMetadata;
use Jgut\JsonApi\Mapping\Metadata\ResourceMetadata;
use Jgut\JsonApi\Schema\MetadataSchemaInterface;
use Jgut\Mapping\Exception\DriverException;

/**
 * Mapping definition trait.
 */
trait MappingTrait
{
    use LinksTrait;

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
     * @param array<string, mixed> $mappingData
     *
     * @throws DriverException
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
     * @param array<string, mixed> $mapping
     *
     * @throws DriverException
     *
     * @return ResourceMetadata
     */
    protected function getResourceMetadata(array $mapping): ResourceMetadata
    {
        if (!isset($mapping['class'])) {
            throw new DriverException('Resource class missing');
        }

        $resourceMetadata = (new ResourceMetadata($mapping['class'], $this->getName($mapping)));

        $this->populateIdentifier($resourceMetadata, $mapping);
        $this->populateAttributes($resourceMetadata, $mapping);
        $this->populateRelationships($resourceMetadata, $mapping);
        $this->populateResource($resourceMetadata, $mapping);

        return $resourceMetadata;
    }

    /**
     * Populate identifier.
     *
     * @param ResourceMetadata     $resourceMetadata
     * @param array<string, mixed> $mapping
     */
    protected function populateIdentifier(ResourceMetadata $resourceMetadata, array $mapping): void
    {
        if (!isset($mapping['id'])) {
            throw new DriverException('Resource does not define an id attribute');
        }

        $identifier = \is_string($mapping['id']) && \trim($mapping['id']) !== ''
            ? \trim($mapping['id'])
            : $mapping['id'];

        if (!\is_array($identifier)) {
            $identifier = ['name' => $identifier];
        }

        $mapping['id'] = $identifier;

        $identifier = (new IdentifierMetadata($mapping['class'], $mapping['id']['name']))
            ->setGetter($this->getGetter($mapping['id']));

        $resourceMetadata->setIdentifier($identifier);
    }

    /**
     * Populate resource attributes.
     *
     * @param ResourceMetadata     $resourceMetadata
     * @param array<string, mixed> $mapping
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
     * Get resource attribute metadata.
     *
     * @param string               $class
     * @param array<string, mixed> $mapping
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
     * @param AttributeMetadata    $attribute
     * @param array<string, mixed> $mapping
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
     * @param array<string, mixed> $mapping
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
     * @param array<string, mixed> $mapping
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
     * @param array<string, mixed> $mapping
     *
     * @return string[]
     */
    protected function getGroups(array $mapping): array
    {
        if (!isset($mapping['groups'])) {
            return [];
        }

        $groups = $mapping['groups'];

        foreach ($groups as $group) {
            if (!\is_string($group)) {
                throw new DriverException(
                    \sprintf('Groups must be a string or string array. "%s" given', \gettype($group))
                );
            }
        }

        return $groups;
    }

    /**
     * Populate resource relationships.
     *
     * @param ResourceMetadata     $resourceMetadata
     * @param array<string, mixed> $mapping
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
     * Ger resource relationship metadata.
     *
     * @param array<string, mixed> $mapping
     *
     * @throws DriverException
     *
     * @return RelationshipMetadata
     */
    protected function getRelationshipMetadata(array $mapping): RelationshipMetadata
    {
        if (!isset($mapping['class'])) {
            throw new DriverException('Resource relationship class missing');
        }

        $relationship = new RelationshipMetadata($mapping['class'], $this->getName($mapping));

        $this->populateRelationship($relationship, $mapping);

        return $relationship;
    }

    /**
     * Populate relationship.
     *
     * @param RelationshipMetadata $relationshipMetadata
     * @param array<string, mixed> $mapping
     */
    protected function populateRelationship(RelationshipMetadata $relationshipMetadata, array $mapping): void
    {
        $this->populateAttribute($relationshipMetadata, $mapping);

        $relationshipMetadata->setMeta($this->getMeta($mapping));

        $selfLinkIncluded = $this->isSelfLinkIncluded($mapping);
        if ($selfLinkIncluded !== null) {
            $relationshipMetadata->setSelfLinkIncluded($selfLinkIncluded);
        }
        $relatedLinkIncluded = $this->isRelatedLinkIncluded($mapping);
        if ($relatedLinkIncluded !== null) {
            $relationshipMetadata->setRelatedLinkIncluded($relatedLinkIncluded);
        }

        foreach ($this->getLinks($mapping) as $link) {
            $relationshipMetadata->addLink($link);
        }
    }

    /**
     * Populate resource.
     *
     * @param ResourceMetadata     $resourceMetadata
     * @param array<string, mixed> $mapping
     */
    protected function populateResource(ResourceMetadata $resourceMetadata, array $mapping): void
    {
        $schemaClass = $this->getSchemaClass($mapping);
        if ($schemaClass !== null) {
            if (!\class_exists($schemaClass)
                || !\in_array(MetadataSchemaInterface::class, \class_implements($schemaClass), true)
            ) {
                throw new DriverException(
                    \sprintf(
                        'Schema class "%s" does not exist or does not implement "%s"',
                        $schemaClass,
                        MetadataSchemaInterface::class
                    )
                );
            }

            $resourceMetadata->setSchemaClass($schemaClass);
        }

        $urlPrefix = $this->getUrlPrefix($mapping);
        if ($urlPrefix !== null) {
            $resourceMetadata->setUrlPrefix($urlPrefix);
        }

        $selfLinkIncluded = $this->isSelfLinkIncluded($mapping);
        if ($selfLinkIncluded !== null) {
            $resourceMetadata->setSelfLinkIncluded($selfLinkIncluded);
        }
        $relatedLinkIncluded = $this->isRelatedLinkIncluded($mapping);
        if ($relatedLinkIncluded !== null) {
            $resourceMetadata->setRelatedLinkIncluded($relatedLinkIncluded);
        }

        foreach ($this->getLinks($mapping) as $link) {
            $resourceMetadata->addLink($link);
        }

        $resourceMetadata->setMeta($this->getMeta($mapping));
    }

    /**
     * Get relationship self link default inclusion.
     *
     * @param array<string, mixed> $mapping
     *
     * @return bool|null
     */
    protected function isSelfLinkIncluded(array $mapping): ?bool
    {
        return isset($mapping['selfLinkIncluded']) ? (bool) $mapping['selfLinkIncluded'] : null;
    }

    /**
     * Get relationship related link default inclusion.
     *
     * @param array<string, mixed> $mapping
     *
     * @return bool|null
     */
    protected function isRelatedLinkIncluded(array $mapping): ?bool
    {
        return isset($mapping['relatedLinkIncluded']) ? (bool) $mapping['relatedLinkIncluded'] : null;
    }

    /**
     * Get schema class.
     *
     * @param array<string, mixed> $mapping
     *
     * @return string|null
     */
    protected function getSchemaClass(array $mapping): ?string
    {
        return $mapping['schemaClass'] ?? null;
    }

    /**
     * Get resource URL prefix.
     *
     * @param array<string, mixed> $mapping
     *
     * @return string|null
     */
    protected function getUrlPrefix(array $mapping): ?string
    {
        return $mapping['urlPrefix'] ?? null;
    }

    /**
     * Get name.
     * Or construct from class.
     *
     * @param array<string, mixed> $mapping
     *
     * @return string
     */
    protected function getName(array $mapping): string
    {
        if (isset($mapping['name']) && \trim($mapping['name']) !== '') {
            return \trim($mapping['name']);
        }

        $nameParts = \explode('\\', $mapping['class']);
        /** @var string $name */
        $name = \end($nameParts);

        return \lcfirst($name);
    }

    /**
     * Get links.
     *
     * @param array<string, mixed> $mapping
     *
     * @return LinkMetadata[]
     */
    protected function getLinks(array $mapping): array
    {
        if (!isset($mapping['links'])) {
            return [];
        }

        return $this->getLinksMetadata($mapping['links']);
    }

    /**
     * Get meta data.
     *
     * @param array<string, mixed> $mapping
     *
     * @return array<string, mixed>
     */
    protected function getMeta(array $mapping): array
    {
        if (!isset($mapping['meta'])) {
            return [];
        }

        $meta = $mapping['meta'];

        if ($meta !== [] && \array_keys($meta) === \range(0, \count($meta) - 1)) {
            throw new DriverException('Metadata keys must be all strings');
        }

        return $meta;
    }
}
