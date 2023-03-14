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

class LinkMetadata implements MetadataInterface
{
    use MetaTrait;

    protected string $href;

    protected ?string $title;

    /**
     * @param array<string, mixed> $meta
     */
    public function __construct(string $href, ?string $title = null, array $meta = [])
    {
        $this->href = $href;
        $this->title = $title;
        $this->meta = $meta;
    }

    public function getHref(): string
    {
        return $this->href;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }
}
