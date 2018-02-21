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
 * Resource metadata.
 */
class ResourceMetadata extends AbstractMetadata
{
    /**
     * Schema provider class.
     *
     * @var string
     */
    protected $schemaClass;

    /**
     * Identifier attribute.
     *
     * @var AttributeMetadata
     */
    protected $identifier;

    /**
     * List of attributes.
     *
     * @var AttributeMetadata[]
     */
    protected $attributes = [];

    /**
     * List of relationship attributes.
     *
     * @var RelationshipMetadata[]
     */
    protected $relationships = [];

    /**
     * @var bool
     */
    protected $includeAttributes = true;

    /**
     * Get schema provider class.
     *
     * @return string|null
     */
    public function getSchemaClass(): ?string
    {
        return $this->schemaClass;
    }

    /**
     * Set schema provider class.
     *
     * @param string $schemaClass
     *
     * @throws \InvalidArgumentException
     *
     * @return self
     */
    public function setSchemaClass(string $schemaClass): self
    {
        $this->schemaClass = $schemaClass;

        return $this;
    }

    /**
     * Get identifier attribute.
     *
     * @return AttributeMetadata|null
     */
    public function getIdentifier(): ?AttributeMetadata
    {
        return $this->identifier;
    }

    /**
     * Set identifier metadata.
     *
     * @param AttributeMetadata $identifier
     *
     * @return self
     */
    public function setIdentifier(AttributeMetadata $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get list of attributes.
     *
     * @return AttributeMetadata[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Add attribute.
     *
     * @param AttributeMetadata $attribute
     *
     * @return self
     */
    public function addAttribute(AttributeMetadata $attribute): self
    {
        $this->attributes[$attribute->getName()] = $attribute;

        return $this;
    }

    /**
     * Get list of relationships.
     *
     * @return RelationshipMetadata[]
     */
    public function getRelationships(): array
    {
        return $this->relationships;
    }

    /**
     * Add relationship.
     *
     * @param RelationshipMetadata $relationship
     *
     * @return self
     */
    public function addRelationship(RelationshipMetadata $relationship): self
    {
        $this->relationships[$relationship->getName()] = $relationship;

        return $this;
    }

    /**
     * Get include attributes.
     *
     * @return bool
     */
    public function isIncludeAttributes(): bool
    {
        return $this->includeAttributes;
    }

    /**
     * Set include attributes.
     *
     * @param bool $includeAttributes
     *
     * @return self
     */
    public function setIncludeAttributes(bool $includeAttributes): self
    {
        $this->includeAttributes = $includeAttributes;

        return $this;
    }
}
