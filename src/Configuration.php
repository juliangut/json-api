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

use Jgut\JsonApi\Encoding\Options;
use Jgut\JsonApi\Encoding\OptionsInterface;
use Jgut\JsonApi\Mapping\Driver\DriverFactory;
use Jgut\JsonApi\Mapping\Driver\DriverInterface;
use Jgut\JsonApi\Schema\MetadataSchema;
use Jgut\JsonApi\Schema\Resolver;
use Jgut\Mapping\Metadata\MetadataResolver;

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
     * Metadata resource schema class.
     *
     * @var string
     */
    protected $schemaClass = MetadataSchema::class;

    /**
     * Schema resolver.
     *
     * @var Resolver
     */
    protected $schemaResolver;

    /**
     * JSON encoding options.
     *
     * @var OptionsInterface
     */
    protected $encodingOptions;

    /**
     * URL prefix for links.
     *
     * @var string|null
     */
    private $urlPrefix;

    /**
     * Global JSON API version.
     *
     * @var string|null
     */
    private $jsonApiVersion;

    /**
     * Global meta.
     *
     * @var string[]|null
     */
    private $jsonApiMeta;

    /**
     * Configuration constructor.
     *
     * @param mixed[] $configurations
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $configurations = [])
    {
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
                /** @var callable $callback */
                $callback = [$this, 'set' . \ucfirst($config)];

                \call_user_func($callback, $configurations[$config]);
            }
        }

        if ($this->encodingOptions === null) {
            $this->encodingOptions = new Options();
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
     * @param mixed $source
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
     * Get JSON encoding options.
     *
     * @return OptionsInterface
     */
    public function getEncodingOptions(): OptionsInterface
    {
        return $this->encodingOptions;
    }

    /**
     * Set JSON encoding options.
     *
     * @param OptionsInterface $encodingOptions
     *
     * @return self
     */
    public function setEncodingOptions(OptionsInterface $encodingOptions): self
    {
        $this->encodingOptions = $encodingOptions;

        return $this;
    }

    /**
     * Get URL prefix.
     *
     * @return string|null
     */
    public function getUrlPrefix(): ?string
    {
        return $this->urlPrefix;
    }

    /**
     * Set URL prefix.
     *
     * @param string $urlPrefix
     */
    public function setUrlPrefix(string $urlPrefix): void
    {
        $this->urlPrefix = $urlPrefix;
    }

    /**
     * Get global JSON API version.
     *
     * @return string|null
     */
    public function getJsonApiVersion(): ?string
    {
        return $this->jsonApiVersion;
    }

    /**
     * Set global JSON API version.
     *
     * @param string $jsonApiVersion
     */
    public function setJsonApiVersion(string $jsonApiVersion): void
    {
        $this->jsonApiVersion = $jsonApiVersion;
    }

    /**
     * Get global meta.
     *
     * @return string[]|null
     */
    public function getJsonApiMeta(): ?array
    {
        return $this->jsonApiMeta;
    }

    /**
     * Set global meta.
     *
     * @param string[] $jsonApiMeta
     */
    public function setJsonApiMeta(array $jsonApiMeta): void
    {
        $this->jsonApiMeta = $jsonApiMeta;
    }
}
