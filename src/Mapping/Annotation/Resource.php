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
 * Resource annotation.
 *
 * @Annotation
 * @Target("CLASS")
 */
class Resource extends AbstractAnnotation
{
    /**
     * Resource name.
     *
     * @var string
     *
     * @Required
     */
    protected $name;

    /**
     * Metadata resource schema class.
     *
     * @var string
     */
    protected $schemaClass;

    /**
     * Resource URL.
     *
     * @var string
     */
    protected $url;

    /**
     * Show attributes visibility when included.
     *
     * @var bool
     */
    protected $attributesInInclude = true;

    /**
     * Get resource name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set resource name.
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
     * Get metadata resource schema class.
     *
     * @return string|null
     */
    public function getSchemaClass(): ?string
    {
        return $this->schemaClass;
    }

    /**
     * Set metadata resource schema class.
     *
     * @param string $schemaClass
     *
     * @return self
     */
    public function setSchemaClass(string $schemaClass): self
    {
        $this->schemaClass = $schemaClass;

        return $this;
    }

    /**
     * Get resource URL.
     *
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Set resource URL.
     *
     * @param string $url
     *
     * @return self
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Should attributes be shown when being included.
     *
     * @return bool
     */
    public function hasAttributesInInclude(): bool
    {
        return $this->attributesInInclude;
    }

    /**
     * Set show attributes visibility when included.
     *
     * @param bool $attributesInInclude
     *
     * @return self
     */
    public function setAttributesInInclude(bool $attributesInInclude)
    {
        $this->attributesInInclude = $attributesInInclude;

        return $this;
    }
}
