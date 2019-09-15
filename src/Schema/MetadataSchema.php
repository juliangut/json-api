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
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
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
     * @var string
     */
    protected $resourcesSubUrl;

    /**
     * {@inheritdoc}
     */
    public function __construct(FactoryInterface $factory, ResourceMetadata $resourceMetadata)
    {
        parent::__construct($factory);

        $this->resourceMetadata = $resourceMetadata;

        $urlPrefix = $resourceMetadata->getUrlPrefix();
        $this->resourcesSubUrl = $urlPrefix !== null && \trim($urlPrefix, '/ ') !== ''
            ? '/' . \trim($urlPrefix, '/ ')
            : '/' . $resourceMetadata->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return $this->resourceMetadata->getName();
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

        /** @var callable $callable */
        $callable = [$resource, $this->resourceMetadata->getIdentifier()->getGetter()];

        return (string) \call_user_func($callable);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes($resource): iterable
    {
        $this->assertResourceType($resource);

        $group = $this->resourceMetadata->getGroup();
        $attributes = [];

        foreach ($this->resourceMetadata->getAttributes() as $attribute) {
            $name = $attribute->getName();
            $groups = $attribute->getGroups();

            if ($group === null || \in_array($group, $groups, true)) {
                /** @var callable $callable */
                $callable = [$resource, $attribute->getGetter()];

                $attributes[$name] = \Closure::fromCallable($callable);
            }
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     *
     * @throws SchemaException
     */
    public function getRelationships($resource): iterable
    {
        $this->assertResourceType($resource);

        $group = $this->resourceMetadata->getGroup();
        $relationships = [];

        foreach ($this->resourceMetadata->getRelationships() as $relationship) {
            $name = $relationship->getName();
            $groups = $relationship->getGroups();

            if ($group === null || \in_array($group, $groups, true)) {
                $relationships[$name] = $this->getRelationshipDescription($resource, $relationship);
            }
        }

        return $relationships;
    }

    /**
     * Get relationship description.
     *
     * @param object               $resource
     * @param RelationshipMetadata $relationship
     *
     * @return mixed[]
     */
    private function getRelationshipDescription($resource, RelationshipMetadata $relationship): array
    {
        /** @var callable $callable */
        $callable = [$resource, $relationship->getGetter()];

        $description = [
            SchemaInterface::RELATIONSHIP_DATA => \Closure::fromCallable($callable),
            SchemaInterface::RELATIONSHIP_LINKS_SELF => $relationship->isSelfLinkIncluded(),
            SchemaInterface::RELATIONSHIP_LINKS_RELATED => $relationship->isRelatedLinkIncluded(),
        ];

        $links = $relationship->getLinks();
        if (\count($links) !== 0) {
            $description[SchemaInterface::RELATIONSHIP_LINKS] = $this->normalizeLinks($links);
        }

        $meta = $relationship->getMeta();
        if (\count($meta) !== 0) {
            $description[SchemaInterface::RELATIONSHIP_META] = $meta;
        }

        return $description;
    }

    /**
     * {@inheritdoc}
     */
    public function getLinks($resource): iterable
    {
        return \array_merge(
            [
                LinkInterface::SELF => $this->getSelfLink($resource),
            ],
            $this->resourceMetadata->getLinks()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function hasIdentifierMeta($resource): bool
    {
        $this->assertResourceType($resource);

        return \count($this->resourceMetadata->getIdentifier()->getMeta()) !== 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierMeta($resource)
    {
        $this->assertResourceType($resource);

        return $this->resourceMetadata->getIdentifier()->getMeta();
    }

    /**
     * {@inheritdoc}
     */
    public function hasResourceMeta($resource): bool
    {
        return \count($this->resourceMetadata->getMeta()) !== 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceMeta($resource)
    {
        $this->assertResourceType($resource);

        return $this->resourceMetadata->getMeta();
    }

    /**
     * {@inheritdoc}
     */
    public function isAddSelfLinkInRelationshipByDefault(string $relationshipName): bool
    {
        return $this->resourceMetadata->isSelfLinkIncluded()
            ?? parent::isAddSelfLinkInRelationshipByDefault($relationshipName);
    }

    /**
     * {@inheritdoc}
     */
    public function isAddRelatedLinkInRelationshipByDefault(string $relationshipName): bool
    {
        return $this->resourceMetadata->isRelatedLinkIncluded()
            ?? parent::isAddSelfLinkInRelationshipByDefault($relationshipName);
    }

    /**
     * {@inheritdoc}
     */
    protected function getResourcesSubUrl(): string
    {
        return $this->resourcesSubUrl;
    }

    /**
     * {@inheritdoc}
     *
     * @throws SchemaException
     */
    protected function getSelfSubUrl($resource): string
    {
        $this->assertResourceType($resource);

        return parent::getSelfSubUrl($resource);
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

                return $this->getFactory()
                    ->createLink(false, $href, \count($meta) !== 0, \count($meta) !== 0 ? $meta : null);
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
