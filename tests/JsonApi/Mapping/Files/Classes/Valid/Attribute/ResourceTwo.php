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

namespace Jgut\JsonApi\Tests\Mapping\Files\Classes\Valid\Attribute;

use Jgut\JsonApi\Mapping\Attribute\Attribute;
use Jgut\JsonApi\Mapping\Attribute\Identifier;
use Jgut\JsonApi\Mapping\Attribute\Link;
use Jgut\JsonApi\Mapping\Attribute\LinkRelated;
use Jgut\JsonApi\Mapping\Attribute\ResourceObject;

#[ResourceObject]
#[LinkRelated(false)]
#[Link('/me', 'me')]
#[Link('/you', 'you')]
class ResourceTwo
{
    #[Identifier]
    protected string $uuid;

    #[Attribute(name: null, groups: ['read'])]
    protected string $two;
}
