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

use Closure;
use InvalidArgumentException;
use Jgut\JsonApi\Configuration;
use Jgut\JsonApi\Mapping\Metadata\ResourceObjectMetadata;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use ReflectionClass;

class Resolver
{
    protected Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return Closure(FactoryInterface): SchemaInterface
     */
    public function getSchemaFactory(ResourceObjectMetadata $resource): Closure
    {
        $defaultSchemaClass = $this->configuration->getSchemaClass();

        return static function (FactoryInterface $factory) use (
            $resource,
            $defaultSchemaClass
        ): SchemaInterface {
            $schemaClass = $resource->getSchema() ?? $defaultSchemaClass;

            $reflection = new ReflectionClass($schemaClass);
            if (!$reflection->implementsInterface(SchemaInterface::class)) {
                throw new InvalidArgumentException(sprintf(
                    'Schema class %s must implement %s.',
                    $schemaClass,
                    SchemaInterface::class,
                ));
            }

            return $reflection->implementsInterface(MetadataSchemaInterface::class)
                ? new $schemaClass($factory, $resource)
                : new $schemaClass($factory);
        };
    }
}
