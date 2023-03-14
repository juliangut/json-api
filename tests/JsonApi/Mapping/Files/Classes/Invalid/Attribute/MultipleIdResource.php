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

use Jgut\JsonApi\Mapping\Attribute as JSONAPI;

#[JSONAPI\ResourceObject]
class MultipleIdResource
{
    #[JSONAPI\Identifier]
    protected string $uuid;

    #[JSONAPI\Identifier]
    protected string $objectId;
}
