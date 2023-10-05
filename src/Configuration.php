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

use InvalidArgumentException;
use Jgut\JsonApi\Encoding\OptionsInterface;
use Jgut\JsonApi\Mapping\Driver\DriverFactory;
use Jgut\JsonApi\Mapping\Driver\DriverInterface;
use Jgut\JsonApi\Schema\MetadataSchema;
use Jgut\JsonApi\Schema\Resolver;
use Jgut\Mapping\Metadata\MetadataResolver;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;

/**
 * @phpstan-type Source array{driver?: object, type?: string, path?: string|list<string>}
 */
final class Configuration
{
    public const QUERY_PARAMETERS_REQUEST_KEY = 'JSON_API_query_parameters';

    private string $attributeName = self::QUERY_PARAMETERS_REQUEST_KEY;

    /**
     * @var list<Source>
     */
    private array $sources = [];

    private ?MetadataResolver $metadataResolver = null;

    /**
     * @var class-string<SchemaInterface>
     */
    private string $schemaClass = MetadataSchema::class;

    private ?Resolver $schemaResolver = null;

    private ?OptionsInterface $encodingOptions = null;

    private ?string $urlPrefix = null;

    private ?string $jsonApiVersion = null;

    /**
     * @var array<string>|null
     */
    private ?array $jsonApiMeta = null;

    /**
     * @param array<string, mixed> $configurations
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $configurations = [])
    {
        $configs = array_keys(get_object_vars($this));

        $unknownParameters = array_diff(array_keys($configurations), $configs);
        if (\count($unknownParameters) !== 0) {
            throw new InvalidArgumentException(
                sprintf(
                    'The following configuration parameters are not recognized: %s.',
                    implode(', ', $unknownParameters),
                ),
            );
        }

        foreach ($configs as $config) {
            if (\array_key_exists($config, $configurations)) {
                /** @var callable $callback */
                $callback = [$this, 'set' . ucfirst($config)];

                $callback($configurations[$config]);
            }
        }
    }

    public function getAttributeName(): string
    {
        return $this->attributeName;
    }

    public function setAttributeName(string $attributeName): self
    {
        $this->attributeName = $attributeName;

        return $this;
    }

    /**
     * @return list<Source>
     */
    public function getSources(): array
    {
        return $this->sources;
    }

    /**
     * @param list<Source> $sources
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
     * @param Source|mixed $source
     *
     * @throws InvalidArgumentException
     */
    public function addSource($source): self
    {
        if (!\is_string($source) && !\is_array($source) && !$source instanceof DriverInterface) {
            throw new InvalidArgumentException(sprintf(
                'Mapping source must be a string, array or %s, %s given.',
                DriverInterface::class,
                \is_object($source) ? $source::class : \gettype($source),
            ));
        }

        /** @var Source $source */
        $this->sources[] = $source;

        return $this;
    }

    public function getMetadataResolver(): MetadataResolver
    {
        if ($this->metadataResolver === null) {
            $this->metadataResolver = new MetadataResolver(new DriverFactory());
        }

        return $this->metadataResolver;
    }

    public function setMetadataResolver(MetadataResolver $metadataResolver): self
    {
        $this->metadataResolver = $metadataResolver;

        return $this;
    }

    /**
     * @return class-string<SchemaInterface>
     */
    public function getSchemaClass(): string
    {
        return $this->schemaClass;
    }

    /**
     * @param class-string<SchemaInterface> $schemaClass
     */
    public function setSchemaClass(string $schemaClass): self
    {
        $this->schemaClass = $schemaClass;

        return $this;
    }

    public function getSchemaResolver(): Resolver
    {
        if ($this->schemaResolver === null) {
            $this->schemaResolver = new Resolver($this);
        }

        return $this->schemaResolver;
    }

    public function setSchemaResolver(Resolver $schemaResolver): self
    {
        $this->schemaResolver = $schemaResolver;

        return $this;
    }

    public function getEncodingOptions(): ?OptionsInterface
    {
        return $this->encodingOptions;
    }

    public function setEncodingOptions(OptionsInterface $encodingOptions): self
    {
        $this->encodingOptions = $encodingOptions;

        return $this;
    }

    public function getUrlPrefix(): ?string
    {
        return $this->urlPrefix;
    }

    public function setUrlPrefix(string $urlPrefix): void
    {
        $this->urlPrefix = $urlPrefix;
    }

    public function getJsonApiVersion(): ?string
    {
        return $this->jsonApiVersion;
    }

    public function setJsonApiVersion(string $jsonApiVersion): void
    {
        $this->jsonApiVersion = $jsonApiVersion;
    }

    /**
     * @return array<string>|null
     */
    public function getJsonApiMeta(): ?array
    {
        return $this->jsonApiMeta;
    }

    /**
     * @param array<string> $jsonApiMeta
     */
    public function setJsonApiMeta(array $jsonApiMeta): void
    {
        $this->jsonApiMeta = $jsonApiMeta;
    }
}
