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
 * Link metadata.
 */
class LinkMetadata extends AbstractMetadata
{
    /**
     * Link href.
     *
     * @var string
     */
    protected $href;

    /**
     * Link metadata.
     *
     * @var array<string, mixed>
     */
    protected $meta = [];

    /**
     * LinkMetadata constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct('', $name);
    }

    /**
     * Get href setter.
     *
     * @return string|null
     */
    public function getHref(): ?string
    {
        return $this->href;
    }

    /**
     * Set href setter.
     *
     * @param string $href
     *
     * @return self
     */
    public function setHref(string $href): self
    {
        $this->href = $href;

        return $this;
    }

    /**
     * Get link metadata.
     *
     * @return array<string, mixed>
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * Set link metadata.
     *
     * @param array<string, mixed> $meta
     *
     * @return self
     */
    public function setMeta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }
}
