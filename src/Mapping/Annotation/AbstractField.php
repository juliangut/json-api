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

namespace Jgut\JsonApi\Mapping\Annotation;

use Jgut\Mapping\Annotation\AbstractAnnotation;

abstract class AbstractField extends AbstractAnnotation
{
    protected ?string $name = null;

    protected ?string $getter = null;

    protected ?string $setter = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

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
