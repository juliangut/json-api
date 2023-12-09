<?php

/*
 * (c) 2018-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Mapping\Annotation;

use Jgut\JsonApi\Schema\MetadataSchemaInterface;
use Jgut\Mapping\Annotation\AbstractAnnotation;

/**
 * @Annotation
 *
 * @Target("CLASS")
 */
final class ResourceObject extends AbstractAnnotation
{
    use LinkTrait;
    use MetaTrait;

    protected ?string $name = null;

    protected ?string $prefix = null;

    /**
     * @var class-string<MetadataSchemaInterface>|null
     */
    protected ?string $schema = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @return class-string<MetadataSchemaInterface>|null
     */
    public function getSchema(): ?string
    {
        return $this->schema;
    }

    /**
     * @param class-string<MetadataSchemaInterface> $schema
     */
    public function setSchema(string $schema): self
    {
        $this->schema = $schema;

        return $this;
    }
}
