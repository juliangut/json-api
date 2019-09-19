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

trait MetasTrait
{
    /**
     * Metas.
     *
     * @var array<string, string>
     */
    protected $meta = [];

    /**
     * Get metas.
     *
     * @return array<string, string>
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * Set metas.
     *
     * @param array<string, string> $meta
     *
     * @return self
     */
    public function setMeta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }
}
