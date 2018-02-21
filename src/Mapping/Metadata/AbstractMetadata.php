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

/**
 * Abstract metadata.
 */
abstract class AbstractMetadata implements MetadataInterface
{
    /**
     * Resource class.
     *
     * @var string
     */
    protected $class;

    /**
     * Resource name.
     *
     * @var string
     */
    protected $name;

    /**
     * ResourceMetadata constructor.
     *
     * @param string $class
     * @param string $name
     */
    public function __construct(string $class, string $name)
    {
        $this->class = $class;
        $this->name = $name;
    }

    /**
     * Get resource class.
     *
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Get resource name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
