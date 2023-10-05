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
final class Meta
{
    public function __construct(
        protected string $key,
        protected mixed $value,
    ) {}

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
