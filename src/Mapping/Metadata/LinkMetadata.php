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

use Jgut\Mapping\Metadata\MetadataInterface;

final class LinkMetadata implements MetadataInterface
{
    public function __construct(
        protected string $href,
        protected ?string $title = null,
        /**
         * @var array<string, mixed>
         */
        protected array $meta = [],
    ) {}

    public function getHref(): string
    {
        return $this->href;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @param array<string, mixed> $meta
     */
    public function setMeta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }
}
