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

namespace Jgut\JsonApi\Tests\Files\Annotation\Valid;

use Jgut\JsonApi\Mapping\Annotation as JJA;

/**
 * Example resource.
 *
 * @JJA\Resource(
 *     name="resourceA",
 *     includeAttributes=false
 * )
 */
class ResourceOne
{
    /**
     * @JJA\Id()
     *
     * @var string
     */
    protected $id;

    /**
     * @JJA\Attribute(
     *     name="theOne"
     * )
     *
     * @var string
     */
    protected $one;

    /**
     * @JJA\Relationship()
     *
     * @var ResourceTwo
     */
    protected $relative;
}
