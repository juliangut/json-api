<?php

/*
 * (c) 2018-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Tests\Mapping\Files\Classes\Valid\Annotation;

use Jgut\JsonApi\Mapping\Annotation as JJA;

/**
 * @JJA\ResourceObject(
 *     linkRelated=false,
 *     links={"me"="/me", "you"="/you"}
 * )
 */
class ResourceTwo
{
    /**
     * @JJA\Identifier()
     */
    protected string $uuid;

    /**
     * @JJA\Attribute(groups={"read"})
     */
    protected string $two;
}
