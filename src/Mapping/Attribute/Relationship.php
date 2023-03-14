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

#[Attribute(Attribute::TARGET_PROPERTY)]
class Relationship
{
    use NameTrait {
        NameTrait::__construct as protected nameConstruct;
    }
    use AccessorTrait {
        AccessorTrait::__construct as protected accessorConstruct;
    }

    /**
     * @var array<string>
     */
    protected ?array $groups;

    /**
     * @param array<string>|null $groups
     */
    public function __construct(
        ?string $name = null,
        ?string $getter = null,
        ?string $setter = null,
        ?array $groups = null
    ) {
        $this->nameConstruct($name);
        $this->accessorConstruct($getter, $setter);
        $this->groups = $groups;
    }

    /**
     * @return array<string>|null
     */
    public function getGroups(): ?array
    {
        return $this->groups;
    }
}
