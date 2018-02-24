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
     * Resource URL.
     *
     * @var string
     */
    protected $url;

    /**
     * Attributes visibility when included.
     *
     * @var bool
     */
    protected $attributesInInclude = true;

    /**
     * Encoding group.
     *
     * @var string
     */
    protected $group;

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
        $this->url = '/' . \trim($url, '/ ');

        return $this;
    }

    /**
     * Get attributes visibility when included.
     *
     * @return bool
     */
    public function hasAttributesInInclude(): bool
    {
        return $this->attributesInInclude;
    }

    /**
     * Set attributes visibility when included.
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
     * Get encoding group.
     *
     * @return string|null
     */
    public function getGroup(): ?string
    {
        return $this->group;
    }

    /**
     * Set encoding group.
     *
     * @param string $group
     *
     * @return self
     */
    public function setGroup(string $group): self
    {
        $this->group = $group;

        return $this;
    }
}
