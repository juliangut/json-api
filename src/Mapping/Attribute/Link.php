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

namespace Jgut\JsonApi\Mapping\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Link
{
    protected string $href;

    protected ?string $title;

    /**
     * @var array<string, mixed>|null
     */
    protected ?array $meta;

    /**
     * @param array<string, mixed>|null $meta
     */
    public function __construct(string $href, ?string $title = null, ?array $meta = null)
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

    /**
     * @return array<string, mixed>|null
     */
    public function getMeta(): ?array
    {
        return $this->meta;
    }
}
