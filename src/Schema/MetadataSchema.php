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

use Closure;
use Jgut\JsonApi\Exception\SchemaException;
use Jgut\JsonApi\Mapping\Metadata\LinkMetadata;
use Jgut\JsonApi\Mapping\Metadata\RelationshipMetadata;
use Jgut\JsonApi\Mapping\Metadata\ResourceObjectMetadata;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Schema\BaseLinkInterface;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use Neomerx\JsonApi\Schema\BaseSchema;

class MetadataSchema extends BaseSchema implements MetadataSchemaInterface
{
    public function __construct(
        FactoryInterface $factory,
        protected ResourceObjectMetadata $resourceMetadata,
    ) {
        parent::__construct($factory);
    }

    public function getType(): string
    {
        return $this->resourceMetadata->getName();
    }

    /**
     * @param object|mixed $resource
     *
     * @throws SchemaException
     */
    public function getId($resource): string
    {
        $this->assertResourceType($resource);

        /** @var callable(): mixed $callable */
        $callable = [$resource, $this->resourceMetadata->getIdentifier()->getGetter()];

        $identifier = $callable();
        if (!\is_string($identifier)) {
            throw new SchemaException(sprintf(
                'Identifier of resource "%s" is not a string, "%s" given.',
                $resource::class,
                \gettype($identifier),
            ));
        }

        return $identifier;
    }

    /**
     * @param object|mixed $resource
     *
     * @return iterable<string, Closure(): mixed>
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getAttributes($resource, ContextInterface $context): iterable
    {
        $this->assertResourceType($resource);

        $group = $this->resourceMetadata->getGroup();

        $attributes = [];
        foreach ($this->resourceMetadata->getAttributes() as $attribute) {
            $name = $attribute->getName();
            $groups = $attribute->getGroups();

            if ($group === null || \in_array($group, $groups, true)) {
                /** @var callable(): mixed $callable */
                $callable = [$resource, $attribute->getGetter()];

                $attributes[$name] = Closure::fromCallable($callable);
            }
        }

        return $attributes;
    }

    /**
     * @param object|mixed $resource
     *
     * @return iterable<string, array<int, mixed>>
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        $this->assertResourceType($resource);

        $group = $this->resourceMetadata->getGroup();

        $relationships = [];
        foreach ($this->resourceMetadata->getRelationships() as $relationshipMetadata) {
            $name = $relationshipMetadata->getName();
            $groups = $relationshipMetadata->getGroups();

            if ($group === null || \in_array($group, $groups, true)) {
                $relationships[$name] = $this->getRelationshipDescription($resource, $relationshipMetadata);
            }
        }

        return $relationships;
    }

    /**
     * @return array<int, mixed>
     */
    private function getRelationshipDescription(object $resource, RelationshipMetadata $relationshipMetadata): array
    {
        /** @var callable(): mixed $callable */
        $callable = [$resource, $relationshipMetadata->getGetter()];

        $description = [
            SchemaInterface::RELATIONSHIP_DATA => Closure::fromCallable($callable),
            SchemaInterface::RELATIONSHIP_LINKS_SELF => $relationshipMetadata->isLinkSelf(),
            SchemaInterface::RELATIONSHIP_LINKS_RELATED => $relationshipMetadata->isLinkRelated(),
        ];

        $links = $relationshipMetadata->getLinks();
        if (\count($links) !== 0) {
            $description[SchemaInterface::RELATIONSHIP_LINKS] = $this->normalizeLinks($links);
        }

        $meta = $relationshipMetadata->getMeta();
        if (\count($meta) !== 0) {
            $description[SchemaInterface::RELATIONSHIP_META] = $meta;
        }

        return $description;
    }

    /**
     * @param object|mixed $resource
     *
     * @return iterable<string, LinkInterface>
     */
    public function getLinks($resource): iterable
    {
        $defaultLinks = [];
        if ($this->resourceMetadata->isLinkSelf() !== false) {
            $defaultLinks[BaseLinkInterface::SELF] = $this->getSelfLink($resource);
        }

        return array_merge($defaultLinks, $this->normalizeLinks($this->resourceMetadata->getLinks()));
    }

    /**
     * @param object|mixed $resource
     */
    public function hasIdentifierMeta($resource): bool
    {
        $this->assertResourceType($resource);

        return \count($this->resourceMetadata->getIdentifier()->getMeta()) !== 0;
    }

    /**
     * @param object|mixed $resource
     */
    public function getIdentifierMeta($resource)
    {
        $this->assertResourceType($resource);

        return $this->resourceMetadata->getIdentifier()
            ->getMeta();
    }

    /**
     * @param object|mixed $resource
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function hasResourceMeta($resource): bool
    {
        return \count($this->resourceMetadata->getMeta()) !== 0;
    }

    /**
     * @param object|mixed $resource
     */
    public function getResourceMeta($resource)
    {
        $this->assertResourceType($resource);

        return $this->resourceMetadata->getMeta();
    }

    public function isAddSelfLinkInRelationshipByDefault(string $relationshipName): bool
    {
        return $this->resourceMetadata->isLinkSelf()
            ?? parent::isAddSelfLinkInRelationshipByDefault($relationshipName);
    }

    public function isAddRelatedLinkInRelationshipByDefault(string $relationshipName): bool
    {
        return $this->resourceMetadata->isLinkRelated()
            ?? parent::isAddSelfLinkInRelationshipByDefault($relationshipName);
    }

    protected function getResourcesSubUrl(): string
    {
        $urlPrefix = $this->resourceMetadata->getPrefix();

        return $urlPrefix !== null && trim($urlPrefix, '/ ') !== ''
            ? '/' . trim($urlPrefix, '/ ')
            : '/' . $this->resourceMetadata->getName();
    }

    /**
     * @param object|mixed $resource
     */
    protected function getSelfSubUrl($resource): string
    {
        $this->assertResourceType($resource);

        return parent::getSelfSubUrl($resource);
    }

    /**
     * @param array<string, LinkMetadata> $links
     *
     * @return array<string, LinkInterface>
     */
    private function normalizeLinks(array $links): array
    {
        return array_map(
            function (LinkMetadata $link): LinkInterface {
                /** @var string $href */
                $href = $link->getHref();
                if (preg_match('!^https?://!', $href) === 1) {
                    $href = '/' . ltrim($href, '/');
                }

                $meta = $link->getMeta();

                return $this->getFactory()
                    ->createLink(false, $href, \count($meta) !== 0, \count($meta) !== 0 ? $meta : null);
            },
            $links,
        );
    }

    /**
     * @throws SchemaException
     */
    private function assertResourceType(mixed $resource): void
    {
        $class = $this->resourceMetadata->getClass();

        if (!\is_object($resource) || !is_a($resource, $class)) {
            throw new SchemaException(sprintf(
                '%s is not a "%s".',
                \is_object($resource) ? 'Class "' . $resource::class . '"' : \gettype($resource),
                $class,
            ));
        }
    }
}
