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
use Jgut\JsonApi\Mapping\Metadata\ResourceMetadata;
use Jgut\JsonApi\Schema\MetadataSchema;
use Neomerx\JsonApi\Contracts\Document\ErrorInterface;
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
     * Schema providers.
     *
     * @var MetadataSchema[]
     */
    protected $schemaFactories;

    /**
     * JSON API manager constructor.
     *
     * @param Configuration $configuration
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
     * @param EncodingParametersInterface|null $encodingParameters
     * @param EncoderOptions|null              $encoderOptions
     *
     * @return string
     */
    public function encodeResources(
        $resources,
        ServerRequestInterface $request,
        EncodingParametersInterface $encodingParameters = null,
        EncoderOptions $encoderOptions = null
    ): string {
        if ($encodingParameters === null) {
            $queryParameters = $this->getRequestQueryParameters($request);

            if ($queryParameters instanceof BaseQueryParserInterface) {
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

        return $this->getResourceEncoder($encoderOptions)->encodeData($resources, $encodingParameters);
    }

    /**
     * Encode errors to JSON API.
     *
     * @param ErrorInterface|ErrorCollection $errors
     * @param EncoderOptions|null            $encoderOptions
     *
     * @return string
     */
    public function encodeErrors($errors, EncoderOptions $encoderOptions = null): string
    {
        if (!$errors instanceof ErrorCollection) {
            $errors = (new ErrorCollection())->add($errors);
        }

        return $this->getErrorEncoder($encoderOptions)->encodeErrors($errors);
    }

    /**
     * Get JSON API resource encoder.
     *
     * @param EncoderOptions|null $encoderOptions
     *
     * @return EncoderInterface
     */
    protected function getResourceEncoder(EncoderOptions $encoderOptions = null): EncoderInterface
    {
        $providers = $this->getSchemaFactories();

        $encoder = $this->getEncoder($providers, $encoderOptions);

        $metadata = $this->configuration->getMetadata();
        if ($metadata !== null) {
            $encoder->withMeta($metadata);
        }

        return $encoder;
    }

    /**
     * Get JSON API error encoder.
     *
     * @param EncoderOptions|null $encoderOptions
     *
     * @return EncoderInterface
     */
    protected function getErrorEncoder(EncoderOptions $encoderOptions = null): EncoderInterface
    {
        return $this->getEncoder([], $encoderOptions);
    }

    /**
     * Get JSON API encoder.
     *
     * @param SchemaInterface[]|\Closure[] $schemaProviders
     * @param EncoderOptions               $encoderOptions
     *
     * @return EncoderInterface
     */
    private function getEncoder(array $schemaProviders, EncoderOptions $encoderOptions = null): EncoderInterface
    {
        return $this->factory->createEncoder(
            $this->factory->createContainer($schemaProviders),
            $encoderOptions ?? $this->configuration->getEncoderOptions()
        );
    }

    /**
     * Get schema factories.
     *
     * @return \Closure[]
     */
    protected function getSchemaFactories(): array
    {
        if ($this->schemaFactories === null) {
            $resolver = $this->configuration->getSchemaResolver();

            $schemaFactories = [];

            /** @var ResourceMetadata[] $resources */
            $resources = $this->configuration->getMetadataResolver()->getMetadata($this->configuration->getSources());

            foreach ($resources as $resource) {
                $schemaFactories[$resource->getClass()] = $resolver->getSchemaFactory($resource);
            }

            $this->schemaFactories = $schemaFactories;
        }

        return $this->schemaFactories;
    }
}
