<?php

/*
 * (c) 2018-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Mapping\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
final class LinkRelated
{
    public function __construct(
        protected bool $included = true,
    ) {}

    public function isIncluded(): bool
    {
        return $this->included;
    }
}
