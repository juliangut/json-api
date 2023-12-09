<?php

/*
 * (c) 2018-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Tests\Mapping\Files\Classes\Valid\Attribute;

use Jgut\JsonApi\Mapping\Attribute\Attribute;
use Jgut\JsonApi\Mapping\Attribute\Identifier;
use Jgut\JsonApi\Mapping\Attribute\Link;
use Jgut\JsonApi\Mapping\Attribute\LinkSelf;
use Jgut\JsonApi\Mapping\Attribute\Meta;
use Jgut\JsonApi\Mapping\Attribute\Relationship;
use Jgut\JsonApi\Mapping\Attribute\ResourceObject;
use Jgut\JsonApi\Schema\MetadataSchema;

#[ResourceObject(name: 'resourceA', prefix: 'resource', schema: MetadataSchema::class)]
#[LinkSelf]
#[Meta('first', 'firstValue')]
#[Meta('second', 'secondValue')]
class ResourceOne
{
    #[Identifier(
        name: 'id',
        getter: 'getId',
        setter: 'setId',
    )]
    protected string $uuid;

    #[Attribute('theOne')]
    protected bool $one;

    #[Relationship]
    #[Link('/custom/path', 'custom', ['key' => 'path'])]
    #[Meta('key', 'value')]
    protected ResourceTwo $relative;

    public function getId(): string
    {
        return $this->uuid;
    }

    public function setId(string $uuid): void
    {
        $this->uuid = $uuid;
    }
}
