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

use Attribute as PHPAttribute;

#[PHPAttribute(PHPAttribute::TARGET_PROPERTY)]
class Identifier
{
    use NameTrait {
        NameTrait::__construct as protected nameConstruct;
    }
    use AccessorTrait {
        AccessorTrait::__construct as protected accessorConstruct;
    }

    /**
     * @var array<string, mixed>|null
     */
    protected ?array $meta;

    /**
     * @param array<string, mixed>|null $meta
     */
    public function __construct(
        ?string $name = null,
        ?string $getter = null,
        ?string $setter = null,
        ?array $meta = []
    ) {
        $this->nameConstruct($name);
        $this->accessorConstruct($getter, $setter);
        $this->meta = $meta;
    }
}
