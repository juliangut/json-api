<?php

/*
 * (c) 2018-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Mapping\Metadata;

use Jgut\Mapping\Metadata\MetadataInterface;

abstract class AbstractMetadata implements MetadataInterface
{
    public function __construct(
        /**
         * @var class-string<object> $class
         */
        protected string $class,
        protected string $name,
    ) {}

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
