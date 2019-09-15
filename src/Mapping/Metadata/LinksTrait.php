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

trait LinksTrait
{
    /**
     * Links.
     *
     * @var array<string, LinkMetadata>
     */
    protected $links = [];

    /**
     * Include self link.
     *
     * @var bool|null
     */
    protected $selfLinkIncluded;

    /**
     * Include related link.
     *
     * @var bool|null
     */
    protected $relatedLinkIncluded;

    /**
     * Get links.
     *
     * @return array<string, LinkMetadata>
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * Add link.
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
     * Is self link included.
     *
     * @return bool|null
     */
    public function isSelfLinkIncluded(): ?bool
    {
        return $this->selfLinkIncluded;
    }

    /**
     * Set self link included.
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
     * @return bool|null
     */
    public function isRelatedLinkIncluded(): ?bool
    {
        return $this->relatedLinkIncluded;
    }

    /**
     * Set related link included.
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
}
