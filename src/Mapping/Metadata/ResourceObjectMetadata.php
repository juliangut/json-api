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

use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use RuntimeException;

final class ResourceObjectMetadata extends AbstractMetadata
{
    use LinkTrait;

    /**
     * @var class-string<SchemaInterface>|null
     */
    protected ?string $schema = null;

    protected ?string $prefix = null;

    protected ?string $group = null;

    protected ?IdentifierMetadata $identifier = null;

    /**
     * @var array<AttributeMetadata>
     */
    protected array $attributes = [];

    /**
     * @var array<RelationshipMetadata>
     */
    protected array $relationships = [];

    /**
     * @param class-string<object> $class
     */
    public function __construct(
        protected string $class,
        protected string $name,
        /**
         * @var array<string, mixed>
         */
        protected array $meta = [],
    ) {
        parent::__construct($class, $name);
    }

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
     * @return array<AttributeMetadata>
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
     * @return array<RelationshipMetadata>
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

    /**
     * @return array<string, mixed>
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @param array<string, mixed> $meta
     */
    public function setMeta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }
}
