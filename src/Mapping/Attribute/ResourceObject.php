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
class ResourceObject
{
    use NameTrait {
        __construct as protected nameConstruct;
    }

    protected ?string $prefix;

    /**
     * @var class-string<SchemaInterface>|null
     */
    protected ?string $schema;

    /**
     * @param class-string<SchemaInterface>|null $schema
     */
    public function __construct(?string $name = null, ?string $prefix = null, ?string $schema = null)
    {
        $this->nameConstruct($name);
        $this->prefix = $prefix;
        $this->schema = $schema;
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
