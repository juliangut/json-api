<?php

/*
 * (c) 2018-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Mapping\Metadata;

trait LinkTrait
{
    protected ?bool $linkSelf = null;

    protected ?bool $linkRelated = null;

    /**
     * @var array<LinkMetadata>
     */
    protected array $links = [];

    public function isLinkSelf(): ?bool
    {
        return $this->linkSelf;
    }

    public function setLinkSelf(?bool $linkSelf): self
    {
        $this->linkSelf = $linkSelf;

        return $this;
    }

    public function isLinkRelated(): ?bool
    {
        return $this->linkRelated;
    }

    public function setLinkRelated(?bool $linkRelated): self
    {
        $this->linkRelated = $linkRelated;

        return $this;
    }

    /**
     * @return array<string, LinkMetadata>
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    public function addLink(LinkMetadata $link): self
    {
        $title = $link->getTitle();
        if ($title !== null) {
            $this->links[$title] = $link;
        } else {
            $this->links[] = $link;
        }

        return $this;
    }
}
