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

namespace Jgut\JsonApi\Mapping\Annotation;

/**
 * Relationship attribute annotation.
 *
 * @Annotation
 * @Target("PROPERTY")
 */
class Relationship extends Attribute
{
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
     * @var array<string, string>
     */
    protected $links = [];

    /**
     * Relationship meta.
     *
     * @var array<string, string>
     */
    protected $meta = [];

    /**
     * Should self link be included.
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
     * Should related link be included.
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
     * @return array<string, string>
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * Set relationship links.
     *
     * @param array<string, string> $links
     *
     * @return self
     */
    public function setLinks(array $links): self
    {
        $this->links = $links;

        return $this;
    }

    /**
     * Get relationship meta.
     *
     * @return array<string, string>
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * Set relationship meta.
     *
     * @param array<string, string> $meta
     *
     * @return self
     */
    public function setMeta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }
}
