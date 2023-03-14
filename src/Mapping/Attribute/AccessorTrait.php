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

trait AccessorTrait
{
    protected ?string $getter;

    protected ?string $setter;

    public function __construct(?string $getter = null, ?string $setter = null)
    {
        $this->getter = $getter;
        $this->setter = $setter;
    }

    public function getGetter(): ?string
    {
        return $this->getter;
    }

    public function getSetter(): ?string
    {
        return $this->setter;
    }
}
