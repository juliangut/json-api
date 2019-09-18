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
     * Resource links.
     *
     * @var array<string, string>
     */
    protected $links = [];

    /**
     * Resource meta.
     *
     * @var array<string, string>
     */
    protected $meta = [];

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
     * Get resource links.
     *
     * @return array<string, string>
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * Set resource links.
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

    /**
     * Get resource meta.
     *
     * @return array<string, string>
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * Set resource meta.
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
