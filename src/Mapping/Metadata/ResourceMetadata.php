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
     * Metadata resource schema class.
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
     * Resource URL prefix.
     *
     * @var string
     */
    protected $urlPrefix;

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
     * Resource links.
     *
     * @var array<string, string>
     */
    protected $links = [];

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
        $this->urlPrefix = '/' . \trim($urlPrefix, '/ ');

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
}
