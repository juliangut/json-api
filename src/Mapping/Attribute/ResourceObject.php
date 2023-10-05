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
