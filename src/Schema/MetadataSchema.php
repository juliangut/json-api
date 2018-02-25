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
use Jgut\JsonApi\Mapping\Metadata\RelationshipMetadata;
use Jgut\JsonApi\Mapping\Metadata\ResourceMetadata;
use Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use Neomerx\JsonApi\Schema\BaseSchema;

/**
 * Dynamic metadata based schema.
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

        $url = $resourceMetadata->getUrl();
        if ($url !== null) {
            $this->selfSubUrl = $url;
        }

        parent::__construct($factory);

        $this->isShowAttributesInIncluded = $resourceMetadata->hasAttributesInInclude();
    }

    /**
     * {@inheritdoc}
     *
     * @throws SchemaException
     */
    public function getId($resource): string
    {
        $this->checkResourceType($resource);

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
    public function getAttributes($resource, array $fieldKeysFilter = null): array
    {
        $this->checkResourceType($resource);

        $group = $this->resourceMetadata->getGroup();
        $attributes = [];

        foreach ($this->resourceMetadata->getAttributes() as $attribute) {
            $groups = $attribute->getGroups();
            $name = $attribute->getName();

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
        $this->checkResourceType($resource);

        if (\count($includeRelationships) === 0) {
            return [];
        }

        $group = $this->resourceMetadata->getGroup();
        $relationships = [];

        foreach ($this->resourceMetadata->getRelationships() as $relationship) {
            $groups = $relationship->getGroups();
            $name = $relationship->getName();

            if (\array_key_exists($name, $includeRelationships)
                && ($group === null || \in_array($group, $groups, true))
            ) {
                $relationships[$name] = $this->getRelationshipDescription($resource, $relationship, $isPrimary);
            }
        }

        if (\count($includeRelationships) !== 0) {
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
    protected function getRelationshipDescription(
        $resource,
        RelationshipMetadata $relationship,
        bool $primary
    ): array {
        if (($primary && $relationship->isSelfLinkIncluded())
            || (!$primary && $relationship->isRelatedLinkIncluded())
        ) {
            return [
                self::SHOW_DATA => false,
                self::SHOW_SELF => $primary && $relationship->isSelfLinkIncluded(),
                self::SHOW_RELATED => !$primary && $relationship->isRelatedLinkIncluded(),
            ];
        }

        return [
            self::DATA => function () use ($resource, $relationship) {
                return $resource->{$relationship->getGetter()}();
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getIncludePaths(): array
    {
        return \array_values(\array_filter(\array_map(
            function (RelationshipMetadata $relationship) {
                $group = $this->resourceMetadata->getGroup();
                $name = $relationship->getName();

                return $relationship->isDefaultIncluded()
                    && ($group === null || \in_array($group, $relationship->getGroups(), true))
                    ? $name
                    : null;
            },
            $this->resourceMetadata->getRelationships()
        )));
    }

    /**
     * Check resource type.
     *
     * @param object $resource
     *
     * @throws SchemaException
     */
    private function checkResourceType($resource): void
    {
        if (!\is_a($resource, $this->resourceMetadata->getClass())) {
            throw new SchemaException(
                \sprintf('Class "%s" is not a "%s"', \get_class($resource), $this->resourceMetadata->getClass())
            );
        }
    }
}
