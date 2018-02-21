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

        parent::__construct($factory);

        $this->isShowAttributesInIncluded = $resourceMetadata->isIncludeAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function getId($resource): string
    {
        $idAttribute = $this->resourceMetadata->getIdentifier();
        if ($idAttribute === null) {
            throw new \RuntimeException(
                \sprintf('No id attribute defined for "%s" resource', $this->resourceMetadata->getClass())
            );
        }

        return (string) $resource->{$idAttribute->getGetter()}();
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes($resource, array $fieldKeysFilter = null): array
    {
        $attributes = [];

        foreach ($this->resourceMetadata->getAttributes() as $name => $attribute) {
            $attributes[$name] = $resource->{$attribute->getGetter()}();
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function getRelationships($resource, bool $isPrimary, array $includeRelationships): array
    {
        if (\count($includeRelationships) === 0) {
            return [];
        }

        $relationships = $this->resourceMetadata->getRelationships();

        $unknownRelationships = \array_diff(\array_keys($includeRelationships), \array_keys($relationships));
        if (\count($unknownRelationships) !== 0) {
            throw new \LogicException(
                \sprintf(
                    'Requested include relationship%s "%s" does not exist',
                    \count($unknownRelationships) > 1 ? 's' : '',
                    \implode('", "', $unknownRelationships)
                )
            );
        }

        return \array_map(
            function (RelationshipMetadata $relationship) use ($resource) {
                return [
                    self::DATA => function () use ($resource, $relationship) {
                        return $resource->{$relationship->getGetter()}();
                    },
                    self::SHOW_SELF => $relationship->isSelfLinkIncluded(),
                    self::SHOW_RELATED => $relationship->isRelatedLinkIncluded(),
                ];
            },
            \array_intersect_key($relationships, $includeRelationships)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIncludePaths(): array
    {
        return \array_values(\array_filter(\array_map(
            function (RelationshipMetadata $relationship) {
                return $relationship->isDefaultIncluded() ? $relationship->getName() : null;
            },
            $this->resourceMetadata->getRelationships()
        )));
    }
}
