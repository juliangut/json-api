<?php

/*
 * (c) 2018-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Mapping\Attribute;

use Attribute;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;

#[Attribute(Attribute::TARGET_CLASS)]
final class ResourceObject
{
    public function __construct(
        protected ?string $name = null,
        protected ?string $prefix = null,
        /**
         * @var class-string<SchemaInterface>|null
         */
        protected ?string $schema = null,
    ) {}

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    /**
     * @return class-string<SchemaInterface>|null
     */
    public function getSchema(): ?string
    {
        return $this->schema;
    }
}
