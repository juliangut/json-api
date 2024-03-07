<?php

/*
 * (c) 2018-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Mapping\Metadata;

abstract class AbstractFieldMetadata extends AbstractMetadata
{
    protected ?string $getter = null;

    protected ?string $setter = null;

    public function getGetter(): ?string
    {
        return $this->getter;
    }

    public function setGetter(string $getter): self
    {
        $this->getter = $getter;

        return $this;
    }

    public function getSetter(): ?string
    {
        return $this->setter;
    }

    public function setSetter(string $setter): self
    {
        $this->setter = $setter;

        return $this;
    }
}
