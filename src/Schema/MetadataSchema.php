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

namespace Jgut\JsonApi\Schema;

use Jgut\JsonApi\Exception\SchemaException;
use Jgut\JsonApi\Mapping\Metadata\LinkMetadata;
use Jgut\JsonApi\Mapping\Metadata\RelationshipMetadata;
use Jgut\JsonApi\Mapping\Metadata\ResourceMetadata;
use Neomerx\JsonApi\Contracts\Document\LinkInterface;
use Neomerx\JsonApi\Contracts\Schema\ResourceObjectInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use Neomerx\JsonApi\Schema\BaseSchema;

/**
 * Resource metadata based schema.
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class MetadataSchema extends BaseSchema implements MetadataSchemaInterface
{
    /**
     * Resource metadata.
     *
     * @var ResourceMetadata
     */
    protected $resourceMetadata;

    /**
     * {@inheritdoc}
     */
    public function __construct(SchemaFactoryInterface $factory, ResourceMetadata $resourceMetadata)
    {
        $this->resourceMetadata = $resourceMetadata;
        $this->resourceType = $resourceMetadata->getName();

        $urlPrefix = $resourceMetadata->getUrlPrefix();
        $this->selfSubUrl = $urlPrefix !== null && \trim($urlPrefix, '/ ') !== ''
            ? '/' . \trim($urlPrefix, '/ ')
            : '/' . $this->resourceType;

        parent::__construct($factory);

        $this->isShowAttributesInIncluded = $resourceMetadata->hasAttributesInInclude();
    }

    /**
     * {@inheritdoc}
     *
     * @throws SchemaException
     */
    public function getSelfSubUrl($resource = null): string
    {
        if ($resource !== null) {
            $this->assertResourceType($resource);
        }

        return parent::getSelfSubUrl($resource);
    }

    /**
     * Get resource identity.
     *
     * @param object $resource
     *
     * @throws SchemaException
     *
     * @return string
     */
    public function getId($resource): string
    {
        $this->assertResourceType($resource);

        $idAttribute = $this->resourceMetadata->getIdentifier();
        if ($idAttribute === null) {
            throw new SchemaException(
                \sprintf('No id attribute defined for "%s" resource', $this->resourceMetadata->getClass())
            );
        }

        return (string) $resource->{$idAttribute->getGetter()}();
    }

    /**
     * {@inheritdoc}
     *
     * @throws SchemaException
     */
    public function getSelfSubLink($resource): LinkInterface
    {
        $this->assertResourceType($resource);

        return parent::getSelfSubLink($resource);
    }

    /**
     * {@inheritdoc}
     *
     * @throws SchemaException
     */
    public function getRelationshipSelfLink(
        $resource,
        string $name,
        $meta = null,
        bool $treatAsHref = false
    ): LinkInterface {
        $this->assertResourceType($resource);

        return parent::getRelationshipSelfLink($resource, $name, $meta, $treatAsHref);
    }

    /**
     * {@inheritdoc}
     */
    public function getRelationshipRelatedLink(
        $resource,
        string $name,
        $meta = null,
        bool $treatAsHref = false
    ): LinkInterface {
        $this->assertResourceType($resource);

        return parent::getRelationshipRelatedLink($resource, $name, $meta, $treatAsHref);
    }

    /**
     * Get resource attributes.
     *
     * @param object        $resource
     * @param string[]|null $fieldKeysFilter
     *
     * @throws SchemaException
     *
     * @return array<string, string>
     */
    public function getAttributes($resource, array $fieldKeysFilter = null): array
    {
        $this->assertResourceType($resource);

        if (\is_array($fieldKeysFilter) && \count($fieldKeysFilter) === 0) {
            return [];
        }

        $group = $this->resourceMetadata->getGroup();
        $attributes = [];

        foreach ($this->resourceMetadata->getAttributes() as $attribute) {
            $name = $attribute->getName();
            $groups = $attribute->getGroups();

            if (($fieldKeysFilter === null || \in_array($name, $fieldKeysFilter, true))
                && ($group === null || \in_array($group, $groups, true))
            ) {
                $attributes[$name] = $resource->{$attribute->getGetter()}();
            }
        }

        if ($fieldKeysFilter !== null) {
            $unknownAttributes = \array_diff($fieldKeysFilter, \array_keys($attributes));
            if (\count($unknownAttributes) !== 0) {
                throw new SchemaException(
                    \sprintf(
                        'Requested attribute%s "%s" does not exist',
                        \count($unknownAttributes) > 1 ? 's' : '',
                        \implode('", "', $unknownAttributes)
                    )
                );
            }
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     *
     * @throws SchemaException
     */
    public function getRelationships($resource, bool $isPrimary, array $includeRelationships): array
    {
        $this->assertResourceType($resource);

        if (\count($includeRelationships) === 0) {
            return [];
        }

        $group = $this->resourceMetadata->getGroup();
        $relationships = [];

        foreach ($this->resourceMetadata->getRelationships() as $relationship) {
            $name = $relationship->getName();
            $groups = $relationship->getGroups();

            if (\array_key_exists($name, $includeRelationships)
                && ($group === null || \in_array($group, $groups, true))
            ) {
                $relationships[$name] = $this->getRelationshipDescription($resource, $relationship, $isPrimary);
            }
        }

        $unknownRelationships = \array_diff_key($includeRelationships, $relationships);
        if (\count($unknownRelationships) !== 0) {
            throw new SchemaException(
                \sprintf(
                    'Requested relationship%s "%s" does not exist',
                    \count($unknownRelationships) > 1 ? 's' : '',
                    \implode('", "', \array_keys($unknownRelationships))
                )
            );
        }

        return $relationships;
    }

    /**
     * Get relationship description.
     *
     * @param object               $resource
     * @param RelationshipMetadata $relationship
     * @param bool                 $primary
     *
     * @return mixed[]
     */
    private function getRelationshipDescription(
        $resource,
        RelationshipMetadata $relationship,
        bool $primary
    ): array {
        $description = [];

        if (($primary && $relationship->isSelfLinkIncluded())
            || (!$primary && $relationship->isRelatedLinkIncluded())
        ) {
            $description[self::SHOW_DATA] = false;
            $description[self::SHOW_SELF] = $primary && $relationship->isSelfLinkIncluded();
            $description[self::SHOW_RELATED] = !$primary && $relationship->isRelatedLinkIncluded();
        } else {
            $description[self::DATA] = function () use ($resource, $relationship) {
                return $resource->{$relationship->getGetter()}();
            };
        }

        if ($primary) {
            $description[self::LINKS] = $this->normalizeLinks($relationship->getLinks());
        }

        // TODO This should be moved to self::getRelationshipsPrimaryMeta
        $meta = $relationship->getMeta();
        if (\count($meta) !== 0) {
            $description[self::META] = $meta;
        }

        return $description;
    }

    /**
     * {@inheritdoc}
     *
     * @throws SchemaException
     */
    public function createResourceObject(
        $resource,
        bool $isOriginallyArrayed,
        array $fieldKeysFilter = null
    ): ResourceObjectInterface {
        $this->assertResourceType($resource);

        return parent::createResourceObject($resource, $isOriginallyArrayed, $fieldKeysFilter);
    }

    /**
     * Get links related to resource.
     *
     * @param object $resource
     *
     * @throws SchemaException
     *
     * @return array<string, LinkInterface>
     */
    public function getResourceLinks($resource): array
    {
        $this->assertResourceType($resource);

        return \array_merge(
            [
                LinkInterface::SELF => $this->getSelfSubLink($resource),
            ],
            $this->normalizeLinks($this->resourceMetadata->getLinks())
        );
    }

    /**
     * Get links related to resource when it is in 'included' section.
     *
     * @param object $resource
     *
     * @throws SchemaException
     *
     * @return array<string, LinkInterface>
     */
    public function getIncludedResourceLinks($resource): array
    {
        $this->assertResourceType($resource);

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getIncludePaths(): array
    {
        return \array_values(\array_filter(\array_map(
            function (RelationshipMetadata $relationship) {
                $name = $relationship->getName();
                $group = $this->resourceMetadata->getGroup();

                return $relationship->isDefaultIncluded()
                && ($group === null || \in_array($group, $relationship->getGroups(), true))
                    ? $name
                    : null;
            },
            $this->resourceMetadata->getRelationships()
        )));
    }

    /**
     * {@inheritdoc}
     *
     * @throws SchemaException
     */
    public function getPrimaryMeta($resource)
    {
        $this->assertResourceType($resource);

        $meta = $this->resourceMetadata->getMeta();
        return \count($meta) !== 0 ? $meta : null;
    }

    /**
     * {@inheritdoc}
     *
     * @throws SchemaException
     */
    public function getLinkageMeta($resource)
    {
        $this->assertResourceType($resource);

        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @throws SchemaException
     */
    public function getInclusionMeta($resource)
    {
        $this->assertResourceType($resource);

        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @throws SchemaException
     */
    public function getRelationshipsPrimaryMeta($resource)
    {
        $this->assertResourceType($resource);

        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @throws SchemaException
     */
    public function getRelationshipsInclusionMeta($resource)
    {
        $this->assertResourceType($resource);

        return null;
    }

    /**
     * Normalize links format.
     *
     * @param array<string, LinkMetadata> $links
     *
     * @return array<string, LinkInterface>
     */
    private function normalizeLinks(array $links): array
    {
        return \array_map(
            function (LinkMetadata $link): LinkInterface {
                /** @var string $href */
                $href = $link->getHref();
                $isExternal = \preg_match('!^https?://!', $href) === false;
                if ($isExternal) {
                    $href = '/' . \ltrim($href, '/');
                }

                $meta = $link->getMeta();

                return $this->createLink($href, \count($meta) !== 0 ? $meta : null, $isExternal);
            },
            $links
        );
    }

    /**
     * Check resource type.
     *
     * @param object $resource
     *
     * @throws SchemaException
     */
    private function assertResourceType($resource): void
    {
        if (!\is_a($resource, $this->resourceMetadata->getClass())) {
            throw new SchemaException(
                \sprintf('Class "%s" is not a "%s"', \get_class($resource), $this->resourceMetadata->getClass())
            );
        }
    }
}
