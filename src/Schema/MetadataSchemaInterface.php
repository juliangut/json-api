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

use Jgut\JsonApi\Mapping\Metadata\ResourceObjectMetadata;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;

interface MetadataSchemaInterface extends SchemaInterface
{
    public function __construct(FactoryInterface $factory, ResourceObjectMetadata $resourceMetadata);
}
