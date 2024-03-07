<?php

/*
 * (c) 2018-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi;

use Closure;
use InvalidArgumentException;
use Jgut\JsonApi\Encoding\FactoryInterface;
use Jgut\JsonApi\Encoding\Options;
use Jgut\JsonApi\Encoding\OptionsInterface;
use Jgut\JsonApi\Exception\SchemaException;
use Jgut\JsonApi\Mapping\Metadata\ResourceObjectMetadata;
use Jgut\Mapping\Metadata\MetadataInterface;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface as BaseFactoryInterface;
use Neomerx\JsonApi\Contracts\Http\Query\BaseQueryParserInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use Neomerx\JsonApi\Schema\ErrorCollection;
use Psr\Http\Message\ServerRequestInterface;
use Traversable;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Manager
{
    public function __construct(
        protected Configuration $configuration,
        protected FactoryInterface $factory,
    ) {}

    public function getFactory(): FactoryInterface
    {
        return $this->factory;
    }

    public function getRequestQueryParameters(ServerRequestInterface $request): ?BaseQueryParserInterface
    {
        $parser = $request->getAttribute($this->configuration->getAttributeName());
        if ($parser !== null && !$parser instanceof BaseQueryParserInterface) {
            throw new InvalidArgumentException(sprintf(
                'Query parameters parser from request is not a %s. "%s" given.',
                BaseQueryParserInterface::class,
                \is_object($parser) ? $parser::class : \gettype($parser),
            ));
        }

        return $parser;
    }

    public function setRequestQueryParameters(
        ServerRequestInterface $request,
        BaseQueryParserInterface $queryParameterParser,
    ): ServerRequestInterface {
        return $request->withAttribute($this->configuration->getAttributeName(), $queryParameterParser);
    }

    /**
     * @param object|list<object>    $resources
     * @param list<non-empty-string> $resourceTypes
     *
     * @throws SchemaException
     */
    final public function encodeResources(
        $resources,
        ServerRequestInterface $request,
        array $resourceTypes = [],
        ?OptionsInterface $encodingOptions = null,
    ): string {
        $queryParameters = $this->getRequestQueryParameters($request);

        $encodingOptions ??= ($this->configuration->getEncodingOptions() ?? new Options());

        /** @var iterable<non-empty-string>|null $includes */
        $includes = $queryParameters?->getIncludes();
        /** @var iterable<string, non-empty-string>|null $fieldSets */
        $fieldSets = $queryParameters?->getFields();

        $encoder = $this->getEncoder(
            $this->getSchemaFactories($resourceTypes, $encodingOptions->getGroup()),
            $encodingOptions,
            $includes,
            $fieldSets,
        );

        return $encoder->encodeData($resources);
    }

    final public function encodeErrors(ErrorCollection $errors, ?OptionsInterface $encodingOptions = null): string
    {
        if ($encodingOptions !== null) {
            $encodingOptions = clone $encodingOptions;
        } else {
            $encodingOptions = $this->configuration->getEncodingOptions() !== null
                ? clone $this->configuration->getEncodingOptions()
                : new Options();
        }
        $encodingOptions->setEncodeOptions($encodingOptions->getEncodeOptions() | \JSON_PARTIAL_OUTPUT_ON_ERROR);

        return $this->getEncoder([], $encodingOptions)->encodeErrors($errors);
    }

    /**
     * @param list<non-empty-string> $resourceTypes
     * @param non-empty-string|null  $group
     *
     * @return list<Closure(BaseFactoryInterface): SchemaInterface>
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

                $schemaFactories[] = $resolver->getSchemaFactory($resource);
            }
        }

        return $schemaFactories;
    }

    /**
     * @return list<ResourceObjectMetadata>
     */
    private function getResourceMetadata(): array
    {
        $mappingSources = $this->configuration->getSources();

        return array_values(array_filter(
            $this->configuration->getMetadataResolver()
                ->getMetadata($mappingSources),
            static fn(MetadataInterface $metadata): bool => $metadata instanceof ResourceObjectMetadata,
        ));
    }

    /**
     * @param list<Closure(BaseFactoryInterface): SchemaInterface|SchemaInterface> $schemaFactories
     * @param iterable<non-empty-string>|null                                      $includePaths
     * @param iterable<string, non-empty-string>|null                              $fieldSets
     *
     * @throws SchemaException
     */
    private function getEncoder(
        array $schemaFactories,
        OptionsInterface $encodingOptions,
        ?iterable $includePaths = null,
        ?iterable $fieldSets = null,
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

        $meta = $encodingOptions->getMeta();
        if ($meta !== null) {
            $encoder->withMeta($meta);
        }

        if ($includePaths !== null) {
            $encoder->withIncludedPaths($includePaths);
        }
        if ($fieldSets !== null) {
            if ($fieldSets instanceof Traversable) {
                $fieldSets = iterator_to_array($fieldSets);
            }

            $encoder->withFieldSets($fieldSets);
        }

        return $encoder;
    }
}
