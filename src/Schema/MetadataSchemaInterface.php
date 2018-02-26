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

namespace Jgut\JsonApi\Schema;

use Jgut\JsonApi\Mapping\Metadata\ResourceMetadata;
use Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;

/**
 * Resource metadata schema interface.
 */
interface MetadataSchemaInterface extends SchemaInterface
{
    /**
     * Metadata resource schema constructor.
     *
     * @param SchemaFactoryInterface $factory
     * @param ResourceMetadata       $resourceMetadata
     */
    public function __construct(SchemaFactoryInterface $factory, ResourceMetadata $resourceMetadata);
}
