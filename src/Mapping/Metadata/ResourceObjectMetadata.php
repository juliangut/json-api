<?php

/*
 * (c) 2018-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Mapping\Metadata;

use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use RuntimeException;

final class ResourceObjectMetadata extends AbstractMetadata
{
    use LinkTrait;
    use MetaTrait;

    /**
     * @var class-string<SchemaInterface>|null
     */
    protected ?string $schema = null;

    protected ?string $prefix = null;

    protected ?string $group = null;

    protected ?IdentifierMetadata $identifier = null;

    /**
     * @var array<string, AttributeMetadata>
     */
    protected array $attributes = [];

    /**
     * @var array<string, RelationshipMetadata>
     */
    protected array $relationships = [];

    /**
     * @return class-string<SchemaInterface>|null
     */
    public function getSchema(): ?string
    {
        return $this->schema;
    }

    /**
     * @param class-string<SchemaInterface> $schema
     */
    public function setSchema(string $schema): self
    {
        $this->schema = $schema;

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

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function setGroup(string $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function hasIdentifier(): bool
    {
        return $this->identifier !== null;
    }

    /**
     * @throws RuntimeException
     */
    public function getIdentifier(): IdentifierMetadata
    {
        if ($this->identifier === null) {
            throw new RuntimeException(
                sprintf('Resource "%s" does not define an identifier.', $this->class),
            );
        }

        return $this->identifier;
    }

    public function setIdentifier(IdentifierMetadata $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return array<string, AttributeMetadata>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function addAttribute(AttributeMetadata $attribute): self
    {
        $this->attributes[$attribute->getName()] = $attribute;

        return $this;
    }

    /**
     * @return array<string, RelationshipMetadata>
     */
    public function getRelationships(): array
    {
        return $this->relationships;
    }

    public function addRelationship(RelationshipMetadata $relationship): self
    {
        $this->relationships[$relationship->getName()] = $relationship;

        return $this;
    }
}
