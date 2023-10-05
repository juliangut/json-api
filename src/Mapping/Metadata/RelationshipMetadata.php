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

final class RelationshipMetadata extends AbstractFieldMetadata
{
    use LinkTrait;

    /**
     * @param class-string<object> $class
     */
    public function __construct(
        string $class,
        string $name,
        /**
         * @var array<string>
         */
        protected array $groups = [],
        /**
         * @var array<string, mixed>
         */
        protected array $meta = [],
    ) {
        parent::__construct($class, $name);
    }

    /**
     * @return array<string>
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @param array<string> $groups
     */
    public function setGroups(array $groups): self
    {
        $this->groups = $groups;

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
