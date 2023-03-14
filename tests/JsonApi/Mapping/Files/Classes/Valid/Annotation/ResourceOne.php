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

namespace Jgut\JsonApi\Tests\Mapping\Files\Classes\Valid\Annotation;

use Jgut\JsonApi\Mapping\Annotation as JJA;

/**
 * @JJA\ResourceObject(
 *     name="resourceA",
 *     schema="Jgut\JsonApi\Schema\MetadataSchema",
 *     prefix="resource",
 *     linkSelf=true,
 *     meta={"first"="firstValue", "second"="secondValue"}
 * )
 */
class ResourceOne
{
    /**
     * @JJA\Identifier(
     *     name="id",
     *     getter="getId",
     *     setter="setId"
     * )
     */
    protected string $uuid;

    /**
     * @JJA\Attribute(name="theOne")
     */
    protected bool $one;

    /**
     * @JJA\Relationship(
     *     links={"custom"={"href"="/custom/path", "meta"={"key"="path"}}},
     *     meta={"key"="value"}
     * )
     */
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
