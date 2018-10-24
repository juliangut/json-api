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

        $resource = (new ResourceMetadata($mapping['class'], $this->getName($mapping)))
            ->setIdentifier($this->getIdentifier($mapping))
            ->setAttributesInInclude($this->hasAttributesInInclude($mapping))
            ->setMeta($this->getMeta($mapping));

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

            $resource->setSchemaClass($schemaClass);
        }

        $urlPrefix = $this->getUrlPrefix($mapping);
        if ($urlPrefix !== null) {
            $resource->setUrlPrefix($urlPrefix);
        }

        $this->populateAttributes($resource, $mapping);
        $this->populateRelationships($resource, $mapping);

        foreach ($this->getLinks($mapping) as $link) {
            $resource->addLink($link);
        }

        return $resource;
    }

    /**
     * Get resource identifier.
     *
     * @param array<string, mixed> $mapping
     *
     * @return IdentifierMetadata
     */
    protected function getIdentifier(array $mapping): IdentifierMetadata
    {
        $identifier = 'id';
        if (\array_key_exists('id', $mapping)) {
            $identifier = \is_string($mapping['id']) && \trim($mapping['id']) !== ''
                ? \trim($mapping['id'])
                : $mapping['id'];
        }

        if (!\is_array($identifier)) {
            $identifier = ['name' => $identifier];
        }

        $mapping['id'] = $identifier;

        return (new IdentifierMetadata($mapping['class'], $mapping['id']['name']))
            ->setGetter($this->getGetter($mapping['id']));
    }

    /**
     * Set attributes visibility when being included.
     *
     * @param array<string, mixed> $mapping
     *
     * @return bool
     */
    protected function hasAttributesInInclude(array $mapping): bool
    {
        return (bool) ($mapping['attributesInInclude'] ?? true);
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
     * Populate resource attributes.
     *
     * @param ResourceMetadata     $resourceMetadata
     * @param array<string, mixed> $mapping
     */
    protected function populateAttributes(ResourceMetadata $resourceMetadata, array $mapping): void
    {
        // @codeCoverageIgnoreStart
        if (!\array_key_exists('attributes', $mapping)) {
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
     * @return array
     */
    protected function getGroups(array $mapping): array
    {
        if (!\array_key_exists('groups', $mapping)) {
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
     * @param RelationshipMetadata $relationship
     * @param array<string, mixed> $mapping
     */
    protected function populateRelationship(RelationshipMetadata $relationship, array $mapping): void
    {
        $this->populateAttribute($relationship, $mapping);

        $relationship
            ->setMeta($this->getMeta($mapping))
            ->setDefaultIncluded($this->isDefaultIncluded($mapping))
            ->setSelfLinkIncluded($this->isSelfLinkIncluded($mapping))
            ->setRelatedLinkIncluded($this->isRelatedLinkIncluded($mapping));

        foreach ($this->getLinks($mapping) as $link) {
            $relationship->addLink($link);
        }
    }

    /**
     * Get attribute default inclusion.
     *
     * @param array<string, mixed> $mapping
     *
     * @return bool
     */
    protected function isDefaultIncluded(array $mapping): bool
    {
        return (bool) ($mapping['included'] ?? false);
    }

    /**
     * Get relationship self link default inclusion.
     *
     * @param array<string, mixed> $mapping
     *
     * @return bool
     */
    protected function isSelfLinkIncluded(array $mapping): bool
    {
        return (bool) ($mapping['selfLinkIncluded'] ?? false);
    }

    /**
     * Get relationship related link default inclusion.
     *
     * @param array<string, mixed> $mapping
     *
     * @return bool
     */
    protected function isRelatedLinkIncluded(array $mapping): bool
    {
        return (bool) ($mapping['relatedLinkIncluded'] ?? false);
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
        if (\array_key_exists('name', $mapping) && \trim($mapping['name']) !== '') {
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
        if (!\array_key_exists('links', $mapping)) {
            return [];
        }

        $links = $mapping['links'];

        if ($links !== [] && \array_keys($links) === \range(0, \count($links) - 1)) {
            throw new DriverException('Links keys must be all strings');
        }

        return \array_map(
            function (array $link): LinkMetadata {
                if (!\array_key_exists('name', $link)) {
                    throw new DriverException('Links must have a name');
                }

                if (!\array_key_exists('href', $link)) {
                    throw new DriverException('Links must have an href');
                }

                return (new LinkMetadata($link['name']))
                    ->setHref($link['href'])
                    ->setMeta($link);
            },
            $links
        );
    }

    /**
     * Get metadata.
     *
     * @param array<string, mixed> $mapping
     *
     * @return array<string, mixed>
     */
    protected function getMeta(array $mapping): array
    {
        if (!\array_key_exists('meta', $mapping)) {
            return [];
        }

        $meta = $mapping['meta'];

        if ($meta !== [] && \array_keys($meta) === \range(0, \count($meta) - 1)) {
            throw new DriverException('Metadata keys must be all strings');
        }

        return $meta;
    }
}
