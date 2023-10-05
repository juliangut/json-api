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

namespace Jgut\JsonApi\Tests\Mapping\Files\Classes\Invalid\Attribute;

use Jgut\JsonApi\Mapping\Attribute\Identifier;
use Jgut\JsonApi\Mapping\Attribute\ResourceObject;

#[ResourceObject]
class MultipleIdResource
{
    #[Identifier]
    protected string $uuid;

    #[Identifier]
    protected string $objectId;
}
