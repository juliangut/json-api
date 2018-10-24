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

namespace Jgut\JsonApi\Mapping\Metadata;

/**
 * Identifier metadata.
 */
class IdentifierMetadata extends AbstractMetadata
{
    /**
     * Identifier getter.
     *
     * @var string
     */
    protected $getter;

    /**
     * Get identifier getter.
     *
     * @return string|null
     */
    public function getGetter(): ?string
    {
        return $this->getter;
    }

    /**
     * Set identifier getter.
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
}
