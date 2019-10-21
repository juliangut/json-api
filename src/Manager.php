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

namespace Jgut\JsonApi;

use Jgut\JsonApi\Encoding\FactoryInterface;
use Jgut\JsonApi\Encoding\OptionsInterface;
use Jgut\JsonApi\Exception\SchemaException;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Http\Query\BaseQueryParserInterface;
use Neomerx\JsonApi\Contracts\Schema\ErrorInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use Neomerx\JsonApi\Schema\ErrorCollection;
use Psr\Http\Message\ServerRequestInterface;

/**
 * JSON-API manager.
 */
class Manager
{
    /**
     * JSON API configuration.
     *
     * @var Configuration
     */
    protected $configuration;

    /**
     * JSON API factory.
     *
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * JSON API manager constructor.
     *
     * @param Configuration    $configuration
     * @param FactoryInterface $factory
     */
    public function __construct(Configuration $configuration, FactoryInterface $factory)
    {
        $this->configuration = $configuration;
        $this->factory = $factory;
    }

    /**
     * Get JSON API factory.
     *
     * @return FactoryInterface
     */
    public function getFactory(): FactoryInterface
    {
        return $this->factory;
    }

    /**
     * Get query parameters from request.
     *
     * @param ServerRequestInterface $request
     *
     * @return BaseQueryParserInterface|null
     */
    public function getRequestQueryParameters(ServerRequestInterface $request): ?BaseQueryParserInterface
    {
        return $request->getAttribute($this->configuration->getAttributeName());
    }

    /**
     * Set query parameters on request.
     *
     * @param ServerRequestInterface   $request
     * @param BaseQueryParserInterface $queryParameterParser
     *
     * @return ServerRequestInterface
     */
    public function setRequestQueryParameters(
        ServerRequestInterface $request,
        BaseQueryParserInterface $queryParameterParser
    ): ServerRequestInterface {
        return $request->withAttribute($this->configuration->getAttributeName(), $queryParameterParser);
    }

    /**
     * Encode resources to JSON API.
     *
     * @param object|object[]        $resources
     * @param ServerRequestInterface $request
     * @param string[]               $resourceTypes
     * @param OptionsInterface|null  $encodingOptions
     *
     * @throws SchemaException
     *
     * @return string
     */
    final public function encodeResources(
        $resources,
        ServerRequestInterface $request,
        array $resourceTypes = [],
        ?OptionsInterface $encodingOptions = null
    ): string {
        $queryParameters = $this->getRequestQueryParameters($request);

        $encodingOptions = $encodingOptions ?? $this->configuration->getEncodingOptions();

        $encoder = $this->getEncoder(
            $this->getSchemaFactories($resourceTypes, $encodingOptions->getGroup()),
            $encodingOptions,
            $queryParameters !== null ? $queryParameters->getIncludes() : null,
            $queryParameters !== null ? $queryParameters->getFields() : null
        );

        return $encoder->encodeData($resources);
    }

    /**
     * Encode errors to JSON API.
     *
     * @param ErrorInterface|ErrorCollection $errors
     * @param OptionsInterface               $encodingOptions
     *
     * @throws SchemaException
     *
     * @return string
     */
    final public function encodeErrors($errors, ?OptionsInterface $encodingOptions = null): string
    {
        if (!$errors instanceof ErrorCollection) {
            $errors = (new ErrorCollection())->add($errors);
        }

        $encodingOptions = clone ($encodingOptions ?? $this->configuration->getEncodingOptions());
        $encodingOptions->setEncodeOptions($encodingOptions->getEncodeOptions() | \JSON_PARTIAL_OUTPUT_ON_ERROR);

        return $this->getEncoder([], $encodingOptions)->encodeErrors($errors);
    }

    /**
     * Get schema factories.
     *
     * @param string[]    $resourceTypes
     * @param string|null $group
     *
     * @return \Closure[]
     */
    private function getSchemaFactories(array $resourceTypes, ?string $group): array
    {
        $resolver = $this->configuration->getSchemaResolver();

        $schemaFactories = [];

        foreach ($this->getResourceMetadata() as $resource) {
            if (\count($resourceTypes) === 0 || \in_array($resource->getName(), $resourceTypes, true)) {
                if ($group !== null) {
                    $resource->setGroup($group);
                }

                $schemaFactories[$resource->getClass()] = $resolver->getSchemaFactory($resource);
            }
        }

        return $schemaFactories;
    }

    /**
     * Get list of resources metadata.
     *
     * @return \Jgut\JsonApi\Mapping\Metadata\ResourceMetadata[]
     */
    private function getResourceMetadata(): array
    {
        /** @var \Jgut\JsonApi\Mapping\Metadata\ResourceMetadata[] $resources */
        $resources = $this->configuration->getMetadataResolver()->getMetadata($this->configuration->getSources());

        return $resources;
    }

    /**
     * Get JSON API encoder.
     *
     * @param SchemaInterface[]|\Closure[] $schemaFactories
     * @param OptionsInterface             $encodingOptions
     * @param iterable|null                $includePaths
     * @param iterable|null                $fieldSets
     *
     * @throws SchemaException
     *
     * @return EncoderInterface
     */
    private function getEncoder(
        array $schemaFactories,
        OptionsInterface $encodingOptions,
        ?iterable $includePaths = null,
        ?iterable $fieldSets = null
    ): EncoderInterface {
        $encoder = $this->factory->createEncoder($this->factory->createSchemaContainer($schemaFactories));

        $urlPrefix = $this->configuration->getJsonApiVersion();
        if ($urlPrefix !== null) {
            $encoder->withUrlPrefix($urlPrefix);
        }
        $apiVersion = $this->configuration->getJsonApiVersion();
        if ($apiVersion !== null) {
            $encoder->withJsonApiVersion($apiVersion);
        }
        $apiMeta = $this->configuration->getJsonApiMeta();
        if ($apiMeta !== null) {
            $encoder->withJsonApiMeta($apiMeta);
        }

        $encoder->withEncodeOptions($encodingOptions->getEncodeOptions());
        $encoder->withEncodeDepth($encodingOptions->getEncodeDepth());

        $links = $encodingOptions->getLinks();
        if ($links !== null) {
            $encoder->withLinks($links);
        }
        // TODO add profile ($encoder->withProfile())
        $meta = $encodingOptions->getMeta();
        if ($meta !== null) {
            $encoder->withMeta($meta);
        }

        if ($includePaths !== null) {
            $encoder->withIncludedPaths($includePaths);
        }
        if ($fieldSets !== null) {
            if ($fieldSets instanceof \Traversable) {
                $fieldSets = \iterator_to_array($fieldSets);
            }

            $encoder->withFieldSets($fieldSets);
        }

        return $encoder;
    }
}
