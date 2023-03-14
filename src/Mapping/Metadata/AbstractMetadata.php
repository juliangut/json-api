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

namespace Jgut\JsonApi\Mapping\Metadata;

use Jgut\Mapping\Metadata\MetadataInterface;

abstract class AbstractMetadata implements MetadataInterface
{
    /**
     * @var class-string<object>
     */
    protected string $class;

    protected string $name;

    /**
     * @param class-string<object> $class
     */
    public function __construct(string $class, string $name)
    {
        $this->class = $class;
        $this->name = $name;
    }

    /**
     * @return class-string<object>
     */
    public function getClass(): string
    {
        return $this->class;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
