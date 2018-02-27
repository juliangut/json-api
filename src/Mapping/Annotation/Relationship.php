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
     * Included by default.
     *
     * @var bool
     */
    protected $included = false;

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
     * @var string[]
     */
    protected $links = [];

    /**
     * Is included by default.
     *
     * @return bool
     */
    public function isIncluded(): bool
    {
        return $this->included;
    }

    /**
     * Set included by default.
     *
     * @param bool $included
     *
     * @return self
     */
    public function setIncluded(bool $included): self
    {
        $this->included = $included;

        return $this;
    }

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
     * @return string[]
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * Set relationship links.
     *
     * @param string[] $links
     *
     * @return self
     */
    public function setLinks(array $links): self
    {
        $this->links = $links;

        return $this;
    }
}
