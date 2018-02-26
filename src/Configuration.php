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

use Jgut\JsonApi\Mapping\Driver\DriverFactory;
use Jgut\JsonApi\Mapping\Driver\DriverInterface;
use Jgut\JsonApi\Schema\MetadataSchema;
use Jgut\JsonApi\Schema\Resolver;
use Jgut\Mapping\Metadata\MetadataResolver;
use Neomerx\JsonApi\Encoder\EncoderOptions;

/**
 * JSON API configuration.
 */
class Configuration
{
    public const QUERY_PARAMETERS_REQUEST_KEY = 'JSON_API_query_parameters';

    /**
     * Request attribute name.
     *
     * @var string
     */
    protected $attributeName = self::QUERY_PARAMETERS_REQUEST_KEY;

    /**
     * Mapping sources.
     *
     * @var string[]|mixed[]|DriverInterface[]
     */
    protected $sources = [];

    /**
     * Metadata resolver.
     *
     * @var MetadataResolver
     */
    protected $metadataResolver;

    /**
     * Schema resolver.
     *
     * @var Resolver
     */
    protected $schemaResolver;

    /**
     * JSON API encoder options.
     *
     * @var EncoderOptions
     */
    protected $encoderOptions;

    /**
     * URL prefix for links.
     *
     * @var string
     */
    protected $urlPrefix;

    /**
     * Metadata resource schema class.
     *
     * @var string
     */
    protected $schemaClass = MetadataSchema::class;

    /**
     * General API metadata.
     *
     * @var mixed[]
     */
    protected $metadata;

    /**
     * General API links.
     *
     * @var string[]|\Neomerx\JsonApi\Document\Link[]
     */
    protected $links;

    /**
     * Configuration constructor.
     *
     * @param array|\Traversable $configurations
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($configurations = [])
    {
        if (!\is_iterable($configurations)) {
            throw new \InvalidArgumentException('Configurations must be an iterable');
        }

        $configs = \array_keys(\get_object_vars($this));

        $unknownParameters = \array_diff(\array_keys($configurations), $configs);
        if (\count($unknownParameters) !== 0) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'The following configuration parameters are not recognized: %s',
                    \implode(', ', $unknownParameters)
                )
            );
        }

        foreach ($configs as $config) {
            if (isset($configurations[$config])) {
                $callback = [$this, 'set' . \ucfirst($config)];

                \call_user_func($callback, $configurations[$config]);
            }
        }
    }

    /**
     * Get request attribute name.
     *
     * @return string
     */
    public function getAttributeName(): string
    {
        return $this->attributeName;
    }

    /**
     * Set request attribute name.
     *
     * @param string $attributeName
     *
     * @return self
     */
    public function setAttributeName(string $attributeName): self
    {
        $this->attributeName = $attributeName;

        return $this;
    }

    /**
     * Get mapping sources.
     *
     * @return string[]|mixed[]|DriverInterface[]
     */
    public function getSources(): array
    {
        return $this->sources;
    }

    /**
     * Set mapping sources.
     *
     * @param mixed[] $sources
     *
     * @throws \InvalidArgumentException
     *
     * @return self
     */
    public function setSources(array $sources): self
    {
        $this->sources = [];

        foreach ($sources as $source) {
            $this->addSource($source);
        }

        return $this;
    }

    /**
     * Add mapping source.
     *
     * @param string|mixed[]|DriverInterface[] $source
     *
     * @throws \InvalidArgumentException
     *
     * @return self
     */
    public function addSource($source): self
    {
        if (!\is_string($source) && !\is_array($source) && !$source instanceof DriverInterface) {
            throw new \InvalidArgumentException(\sprintf(
                'Mapping source must be a string, array or %s, %s given',
                DriverInterface::class,
                \is_object($source) ? \get_class($source) : \gettype($source)
            ));
        }

        $this->sources[] = $source;

        return $this;
    }

    /**
     * Get metadata resolver.
     *
     * @return MetadataResolver
     */
    public function getMetadataResolver(): MetadataResolver
    {
        if ($this->metadataResolver === null) {
            $this->metadataResolver = new MetadataResolver(new DriverFactory());
        }

        return $this->metadataResolver;
    }

    /**
     * Set metadata resolver.
     *
     * @param MetadataResolver $metadataResolver
     *
     * @return self
     */
    public function setMetadataResolver(MetadataResolver $metadataResolver): self
    {
        $this->metadataResolver = $metadataResolver;

        return $this;
    }

    /**
     * Get schema resolver.
     *
     * @return Resolver
     */
    public function getSchemaResolver(): Resolver
    {
        if ($this->schemaResolver === null) {
            $this->schemaResolver = new Resolver($this);
        }

        return $this->schemaResolver;
    }

    /**
     * Set schema resolver.
     *
     * @param Resolver $schemaResolver
     *
     * @return self
     */
    public function setSchemaResolver(Resolver $schemaResolver): self
    {
        $this->schemaResolver = $schemaResolver;

        return $this;
    }

    /**
     * Get JSON API encoder options.
     *
     * @return EncoderOptions
     */
    public function getEncoderOptions(): EncoderOptions
    {
        if ($this->encoderOptions === null) {
            $jsonFlags = \JSON_UNESCAPED_UNICODE
                | \JSON_UNESCAPED_SLASHES
                | \JSON_PRESERVE_ZERO_FRACTION;

            $this->encoderOptions = new EncoderOptions(
                $jsonFlags,
                \is_string($this->urlPrefix) ? \rtrim($this->urlPrefix, '/') : null
            );
        }

        return $this->encoderOptions;
    }

    /**
     * Set JSON API encoder options.
     *
     * @param EncoderOptions $encoderOptions
     *
     * @return self
     */
    public function setEncoderOptions(EncoderOptions $encoderOptions): self
    {
        $this->encoderOptions = $encoderOptions;

        return $this;
    }

    /**
     * Get URL prefix for links.
     *
     * @return string
     */
    public function getUrlPrefix(): ?string
    {
        return $this->urlPrefix;
    }

    /**
     * Set URL prefix for links.
     *
     * @param string $urlPrefix
     *
     * @return self
     */
    public function setUrlPrefix(string $urlPrefix): self
    {
        $this->urlPrefix = $urlPrefix;

        return $this;
    }

    /**
     * Get metadata resource schema class.
     *
     * @return string
     */
    public function getSchemaClass(): string
    {
        return $this->schemaClass;
    }

    /**
     * Set metadata resource schema class.
     *
     * @param string $schemaClass
     *
     * @return self
     */
    public function setSchemaClass(string $schemaClass): self
    {
        $this->schemaClass = $schemaClass;

        return $this;
    }

    /**
     * Get general API metadata.
     *
     * @return mixed[]|null
     */
    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    /**
     * Set general API metadata.
     *
     * @param mixed[] $metadata
     *
     * @return self
     */
    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Get general API links.
     *
     * @return string[]|\Neomerx\JsonApi\Document\Link[]
     */
    public function getLinks(): ?array
    {
        return $this->links;
    }

    /**
     * Set general API links.
     *
     * @param string[]|\Neomerx\JsonApi\Document\Link[] $links
     *
     * @return self
     */
    public function setLinks(array $links): self
    {
        $this->links = $links;

        return $this;
    }
}
