<?php

/*
 * (c) 2018-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Mapping\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Identifier
{
    public function __construct(
        protected ?string $name = null,
        protected ?string $getter = null,
        protected ?string $setter = null,
        /**
         * @var array<string, mixed>
         */
        protected array $meta = [],
    ) {}

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getGetter(): ?string
    {
        return $this->getter;
    }

    public function getSetter(): ?string
    {
        return $this->setter;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMeta(): array
    {
        return $this->meta;
    }
}
