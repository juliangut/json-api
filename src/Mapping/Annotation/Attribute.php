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

/**
 * Attribute annotation.
 *
 * @Annotation
 * @Target("PROPERTY")
 */
class Attribute extends AbstractAnnotation
{
    /**
     * Attribute name.
     *
     * @var string
     *
     * @Required
     */
    protected $name;

    /**
     * Attribute getter.
     *
     * @var string
     */
    protected $getter;

    /**
     * Attribute setter.
     *
     * @var string
     */
    protected $setter;

    /**
     * Attribute encoding groups.
     *
     * @var array<string>
     */
    protected $groups = ['default'];

    /**
     * Get attribute name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set attribute name.
     *
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get attribute getter.
     *
     * @return string|null
     */
    public function getGetter(): ?string
    {
        return $this->getter;
    }

    /**
     * Set attribute getter.
     *
     * @param string $getter
     *
     * @return self
     */
    public function setGetter(string $getter): self
    {
        $this->getter = $getter;

        return $this;
    }

    /**
     * Get attribute setter.
     *
     * @return string|null
     */
    public function getSetter(): ?string
    {
        return $this->setter;
    }

    /**
     * Set attribute setter.
     *
     * @param string $setter
     *
     * @return self
     */
    public function setSetter(string $setter): self
    {
        $this->setter = $setter;

        return $this;
    }

    /**
     * Get attribute groups.
     *
     * @return string[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * Set attribute groups.
     *
     * @param string[] $groups
     *
     * @return self
     */
    public function setGroups(array $groups): self
    {
        $this->groups = $groups;

        return $this;
    }
}
