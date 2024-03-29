<?php

/*
 * (c) 2018-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Mapping\Attribute;

use Attribute as PHPAttribute;

#[PHPAttribute(PHPAttribute::TARGET_PROPERTY)]
final class Attribute
{
    public function __construct(
        protected ?string $name = null,
        protected ?string $getter = null,
        protected ?string $setter = null,
        /**
         * @var list<string>
         */
        protected array $groups = [],
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
     * @return list<string>
     */
    public function getGroups(): array
    {
        return $this->groups;
    }
}
