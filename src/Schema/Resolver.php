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

use Jgut\JsonApi\Configuration;
use Jgut\JsonApi\Mapping\Metadata\ResourceMetadata;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;

/**
 * JSON API schema resolver.
 */
class Resolver
{
    /**
     * JSON API configuration.
     *
     * @var Configuration
     */
    protected $configuration;

    /**
     * RouteCompiler constructor.
     *
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Get schema factory callable.
     *
     * @param ResourceMetadata $resource
     *
     * @return \Closure
     */
    public function getSchemaFactory(ResourceMetadata $resource): \Closure
    {
        $defaultSchemaClass = $this->configuration->getSchemaClass();

        return function (FactoryInterface $factory) use ($resource, $defaultSchemaClass
        ): MetadataSchemaInterface {
            $schemaClass = $defaultSchemaClass;

            if ($resource->getSchemaClass() !== null) {
                $schemaClass = $resource->getSchemaClass();

                $reflection = new \ReflectionClass($schemaClass);
                if (!$reflection->implementsInterface(MetadataSchemaInterface::class)) {
                    throw new \InvalidArgumentException(\sprintf(
                        'Schema class %s must implement %s',
                        $schemaClass,
                        MetadataSchemaInterface::class
                    ));
                }
            }

            return new $schemaClass($factory, $resource);
        };
    }
}
