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
     * Resource URL prefix.
     *
     * @var string
     */
    protected $urlPrefix;

    /**
     * Show attributes visibility when included.
     *
     * @var bool
     */
    protected $attributesInInclude = true;

    /**
     * Resource links.
     *
     * @var array<string, string>
     */
    protected $links = [];

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
     * Get resource URL prefix.
     *
     * @return string|null
     */
    public function getUrlPrefix(): ?string
    {
        return $this->urlPrefix;
    }

    /**
     * Set resource URL prefix.
     *
     * @param string $urlPrefix
     *
     * @return self
     */
    public function setUrlPrefix(string $urlPrefix): self
    {
        $this->urlPrefix = $urlPrefix;

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
    public function setAttributesInInclude(bool $attributesInInclude): self
    {
        $this->attributesInInclude = $attributesInInclude;

        return $this;
    }

    /**
     * Get relationship links.
     *
     * @return array<string, string>
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * Set relationship links.
     *
     * @param array<string, string> $links
     *
     * @return self
     */
    public function setLinks(array $links): self
    {
        $this->links = $links;

        return $this;
    }
}
