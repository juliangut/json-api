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

use Attribute as NativeAttribute;

#[NativeAttribute(NativeAttribute::TARGET_PROPERTY)]
final class Attribute
{
    public function __construct(
        protected ?string $name = null,
        protected ?string $getter = null,
        protected ?string $setter = null,
        /**
         * @var array<string>
         */  protected array $groups = [],
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
     * @return array<string>
     */
    public function getGroups(): array
    {
        return $this->groups;
    }
}
