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
use Jgut\JsonApi\Exception\SchemaException;
use Neomerx\JsonApi\Contracts\Document\ErrorInterface;
use Neomerx\JsonApi\Contracts\Document\LinkInterface;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Neomerx\JsonApi\Contracts\Http\Query\BaseQueryParserInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use Neomerx\JsonApi\Encoder\EncoderOptions;
use Neomerx\JsonApi\Encoder\Parameters\EncodingParameters;
use Neomerx\JsonApi\Exceptions\ErrorCollection;
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
     * @param object|object[]                  $resources
     * @param ServerRequestInterface           $request
     * @param string|null                      $group
     * @param string[]                         $resourceTypes
     * @param EncodingParametersInterface|null $encodingParameters
     * @param array<string, LinkInterface>     $links
     * @param array<string, mixed>             $meta
     *
     * @throws SchemaException
     *
     * @return string
     */
    final public function encodeResources(
        $resources,
        ServerRequestInterface $request,
        ?string $group = null,
        array $resourceTypes = [],
        ?EncodingParametersInterface $encodingParameters = null,
        array $links = [],
        array $meta = []
    ): string {
        if ($encodingParameters === null) {
            $queryParameters = $this->getRequestQueryParameters($request);

            if ($queryParameters !== null) {
                $encodingParameters = new EncodingParameters(
                    $queryParameters->getIncludes(),
                    $queryParameters->getFields()
                );
            } else {
                // @codeCoverageIgnoreStart
                $encodingParameters = new EncodingParameters();
                // @codeCoverageIgnoreEnd
            }
        }

        return $this->getResourceEncoder($resourceTypes, $group, $links, $meta)
            ->encodeData($resources, $encodingParameters);
    }

    /**
     * Get JSON API resource encoder.
     *
     * @param string[]                     $resourceTypes
     * @param string|null                  $group
     * @param array<string, LinkInterface> $links
     * @param array<string, mixed>         $meta
     *
     * @throws SchemaException
     *
     * @return EncoderInterface
     */
    protected function getResourceEncoder(
        array $resourceTypes,
        ?string $group,
        array $links,
        array $meta
    ): EncoderInterface {
        $schemaFactories = $this->getSchemaFactories($resourceTypes, $group);

        return $this->getEncoder($schemaFactories, $this->configuration->getEncoderOptions(), $links, $meta);
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
        /* @var \Jgut\JsonApi\Mapping\Metadata\ResourceMetadata[] */
        return $this->configuration->getMetadataResolver()->getMetadata($this->configuration->getSources());
    }

    /**
     * Encode errors to JSON API.
     *
     * @param ErrorInterface|ErrorCollection $errors
     * @param array<string, LinkInterface>   $links
     * @param array<string, mixed>           $meta
     *
     * @throws SchemaException
     *
     * @return string
     */
    final public function encodeErrors($errors, array $links = [], array $meta = []): string
    {
        if (!$errors instanceof ErrorCollection) {
            $errors = (new ErrorCollection())->add($errors);
        }

        return $this->getErrorEncoder($links, $meta)->encodeErrors($errors);
    }

    /**
     * Get JSON API error encoder.
     *
     * @param array<string, LinkInterface> $links
     * @param array<string, mixed>         $meta
     *
     * @throws SchemaException
     *
     * @return EncoderInterface
     */
    protected function getErrorEncoder(array $links, array $meta): EncoderInterface
    {
        $encoderOptions = $this->configuration->getEncoderOptions();
        $encoderOptions = new EncoderOptions(
            $encoderOptions->getOptions() | \JSON_PARTIAL_OUTPUT_ON_ERROR,
            $encoderOptions->getUrlPrefix(),
            $encoderOptions->getDepth()
        );

        return $this->getEncoder([], $encoderOptions, $links, $meta);
    }

    /**
     * Get JSON API encoder.
     *
     * @param SchemaInterface[]|\Closure[] $schemaFactories
     * @param EncoderOptions               $encoderOptions
     * @param mixed[]                      $links
     * @param mixed[]                      $meta
     *
     * @throws SchemaException
     *
     * @return EncoderInterface
     */
    protected function getEncoder(
        array $schemaFactories,
        EncoderOptions $encoderOptions,
        array $links,
        array $meta
    ): EncoderInterface {
        $encoder = $this->factory->createEncoder($this->factory->createContainer($schemaFactories), $encoderOptions);

        if (\count($links) !== 0) {
            foreach ($links as $name => $link) {
                if (!\is_string($name)) {
                    throw new SchemaException('Links keys must be all strings');
                }

                if (!$link instanceof LinkInterface) {
                    throw new SchemaException(\sprintf(
                        'Link must be an instance of %s, %s given',
                        LinkInterface::class,
                        \is_object($link) ? \get_class($link) : \gettype($link)
                    ));
                }
            }

            $encoder->withLinks($links);
        }

        if (\count($meta) !== 0) {
            if (\array_keys($meta) === \range(0, \count($meta) - 1)) {
                throw new SchemaException('Metadata keys must be all strings');
            }

            $encoder->withMeta($meta);
        }

        return $encoder;
    }
}
