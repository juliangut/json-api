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

namespace Jgut\JsonApi\Mapping\Metadata;

/**
 * Relationship attribute metadata.
 */
class RelationshipMetadata extends AttributeMetadata
{
    /**
     * Included by default.
     *
     * @var bool
     */
    protected $defaultIncluded = false;

    /**
     * Include self link.
     *
     * @var bool
     */
    protected $selfLinkIncluded = false;

    /**
     * Include related link.
     *
     * @var bool
     */
    protected $relatedLinkIncluded = false;

    /**
     * Relationship links.
     *
     * @var array<string, LinkMetadata>
     */
    protected $links = [];

    /**
     * Relationship metadata.
     *
     * @var array<string, mixed>
     */
    protected $meta = [];

    /**
     * Is included by default.
     *
     * @return bool
     */
    public function isDefaultIncluded(): bool
    {
        return $this->defaultIncluded;
    }

    /**
     * Set included by default.
     *
     * @param bool $defaultIncluded
     *
     * @return self
     */
    public function setDefaultIncluded(bool $defaultIncluded): self
    {
        $this->defaultIncluded = $defaultIncluded;

        return $this;
    }

    /**
     * Is self link included.
     *
     * @return bool
     */
    public function isSelfLinkIncluded(): bool
    {
        return $this->selfLinkIncluded;
    }

    /**
     * Set self link visibility.
     *
     * @param bool $selfLinkIncluded
     *
     * @return self
     */
    public function setSelfLinkIncluded(bool $selfLinkIncluded): self
    {
        $this->selfLinkIncluded = $selfLinkIncluded;

        return $this;
    }

    /**
     * Is related link included.
     *
     * @return bool
     */
    public function isRelatedLinkIncluded(): bool
    {
        return $this->relatedLinkIncluded;
    }

    /**
     * Set related link visibility.
     *
     * @param bool $relatedLinkIncluded
     *
     * @return self
     */
    public function setRelatedLinkIncluded(bool $relatedLinkIncluded): self
    {
        $this->relatedLinkIncluded = $relatedLinkIncluded;

        return $this;
    }

    /**
     * Get relationship links.
     *
     * @return array<string, LinkMetadata>
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * Add relationship link.
     *
     * @param LinkMetadata $link
     *
     * @return self
     */
    public function addLink(LinkMetadata $link): self
    {
        $this->links[$link->getName()] = $link;

        return $this;
    }

    /**
     * Get relationship metadata.
     *
     * @return array<string, mixed>
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * Set relationship metadata.
     *
     * @param array<string, mixed> $meta
     *
     * @return self
     */
    public function setMeta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }
}
