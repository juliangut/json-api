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

namespace Jgut\JsonApi\Tests\Mapping\Files\Classes\Invalid\Annotation;

use Jgut\JsonApi\Mapping\Annotation as JJA;

/**
 * @JJA\ResourceObject()
 */
class MultipleIdResource
{
    /**
     * @JJA\Identifier()
     */
    protected string $uuid;

    /**
     * @JJA\Identifier()
     */
    protected string $objectId;
}
