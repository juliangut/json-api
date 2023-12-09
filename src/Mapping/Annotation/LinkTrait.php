<?php

/*
 * (c) 2018-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Mapping\Annotation;

trait LinkTrait
{
    protected ?bool $linkSelf = null;

    protected ?bool $linkRelated = null;

    /**
     * @var LinkMapping
     */
    protected array $links = [];

    public function isLinkSelf(): ?bool
    {
        return $this->linkSelf;
    }

    public function setLinkSelf(bool $linkSelf): self
    {
        $this->linkSelf = $linkSelf;

        return $this;
    }

    public function isLinkRelated(): ?bool
    {
        return $this->linkRelated;
    }

    public function setLinkRelated(bool $linkRelated): self
    {
        $this->linkRelated = $linkRelated;

        return $this;
    }

    /**
     * @return LinkMapping
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @param LinkMapping $links
     */
    public function setLinks(array $links): self
    {
        $this->links = $links;

        return $this;
    }
}
