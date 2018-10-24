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
use Neomerx\JsonApi\Contracts\Document\ErrorInterface;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Neomerx\JsonApi\Contracts\Http\Query\BaseQueryParserInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use Neomerx\JsonApi\Document\Link;
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
     *
     * @return string
     */
    final public function encodeResources(
        $resources,
        ServerRequestInterface $request,
        ?string $group = null,
        array $resourceTypes = [],
        ?EncodingParametersInterface $encodingParameters = null
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

        $encoder = $this->getResourceEncoder($resourceTypes, $group);

        $metadata = $this->configuration->getMetadata();
        if ($metadata !== null) {
            $encoder->withMeta($metadata);
        }

        $links = $this->getLinks();
        if (\count($links) !== 0) {
            $encoder->withLinks($links);
        }

        return $encoder->encodeData($resources, $encodingParameters);
    }

    /**
     * Get JSON API resource encoder.
     *
     * @param string[]    $resourceTypes
     * @param string|null $group
     *
     * @return EncoderInterface
     */
    protected function getResourceEncoder(array $resourceTypes, ?string $group): EncoderInterface
    {
        $schemaFactories = $this->getSchemaFactories($resourceTypes, $group);

        return $this->getEncoder($schemaFactories, $this->configuration->getEncoderOptions());
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
     *
     * @return string
     */
    final public function encodeErrors($errors): string
    {
        if (!$errors instanceof ErrorCollection) {
            $errors = (new ErrorCollection())->add($errors);
        }

        return $this->getErrorEncoder()->encodeErrors($errors);
    }

    /**
     * Get JSON API error encoder.
     *
     * @return EncoderInterface
     */
    protected function getErrorEncoder(): EncoderInterface
    {
        $encoderOptions = $this->configuration->getEncoderOptions();
        $encoderOptions = new EncoderOptions(
            $encoderOptions->getOptions() | \JSON_PARTIAL_OUTPUT_ON_ERROR,
            $encoderOptions->getUrlPrefix(),
            $encoderOptions->getDepth()
        );

        return $this->getEncoder([], $encoderOptions);
    }

    /**
     * Get JSON API encoder.
     *
     * @param SchemaInterface[]|\Closure[] $schemaFactories
     * @param EncoderOptions               $encoderOptions
     *
     * @return EncoderInterface
     */
    private function getEncoder(array $schemaFactories, EncoderOptions $encoderOptions): EncoderInterface
    {
        return $this->factory->createEncoder($this->factory->createContainer($schemaFactories), $encoderOptions);
    }

    /**
     * Get general API links.
     *
     * @return string[]|Link[]
     */
    protected function getLinks(): array
    {
        $links = \array_merge(
            $this->configuration->getUrlPrefix() !== null ? ['base' => $this->configuration->getUrlPrefix()] : [],
            $this->configuration->getLinks() ?? []
        );

        return \array_map(
            function ($link) {
                if (\is_string($link)) {
                    $isExternal = \preg_match('!^https?://!', $link) === false;
                    if ($isExternal) {
                        $link = '/' . \ltrim($link, '/');
                    }

                    $link = new Link($link, null, $isExternal);
                }

                return $link;
            },
            $links
        );
    }
}
