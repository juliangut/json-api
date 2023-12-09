<?php

/*
 * (c) 2018-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
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
