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

namespace Jgut\JsonApi\Encoding;

use Jgut\JsonApi\Exception\SchemaException;
use Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use Neomerx\JsonApi\Encoder\Encoder;

/**
 * Custom encoder.
 */
class Options implements OptionsInterface
{
    public const JSON_ENCODE_OPTIONS = \JSON_UNESCAPED_UNICODE
        | \JSON_UNESCAPED_SLASHES
        | \JSON_PRESERVE_ZERO_FRACTION
        | \JSON_HEX_AMP
        | \JSON_HEX_APOS
        | \JSON_HEX_QUOT
        | \JSON_HEX_TAG;

    /**
     * JSON encode options.
     *
     * @var int
     */
    private $encodeOptions = self::JSON_ENCODE_OPTIONS;

    /**
     * JSON encode depth.
     *
     * @var int
     */
    private $encodeDepth = Encoder::DEFAULT_JSON_ENCODE_DEPTH;

    /**
     * @var string|null
     */
    private $group;

    /**
     * @var mixed[]|null
     */
    private $links;

    /**
     * @var string[]|null
     */
    private $meta;

    /**
     * {@inheritdoc}
     */
    public function getEncodeOptions(): int
    {
        return $this->encodeOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function setEncodeOptions(int $encodingOptions): void
    {
        $this->encodeOptions = $encodingOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function getEncodeDepth(): int
    {
        return $this->encodeDepth;
    }

    /**
     * {@inheritdoc}
     */
    public function setEncodeDepth(int $encodingDepth): void
    {
        $this->encodeDepth = $encodingDepth;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroup(): ?string
    {
        return $this->group;
    }

    /**
     * {@inheritdoc}
     */
    public function setGroup(string $group): void
    {
        $this->group = $group;
    }

    /**
     * {@inheritdoc}
     */
    public function getLinks(): ?array
    {
        return $this->links;
    }

    /**
     * {@inheritdoc}
     *
     * @throws SchemaException
     */
    public function setLinks(array $links): void
    {
        if ($links !== [] && \array_keys($links) === \range(0, \count($links) - 1)) {
            throw new SchemaException('Links keys must be all strings');
        }

        $linkList = [];
        foreach ($links as $name => $definition) {
            if ($definition instanceof LinkInterface) {
                $link = $definition;
            } elseif (\is_array($definition)) {
                $href = $definition['href'];
                if (!$href instanceof LinkInterface) {
                    throw new SchemaException(\sprintf(
                        'Link href must be an instance of %s or array, %s given',
                        LinkInterface::class,
                        \is_object($href) ? \get_class($href) : \gettype($href)
                    ));
                }
                $this->assertMeta($definition['meta']);

                $link = [
                    'href' => $definition['href'],
                    'meta' => $definition['meta'],
                ];
            } else {
                throw new SchemaException(\sprintf(
                    'Link must be an instance of %s or array, %s given',
                    LinkInterface::class,
                    \is_object($definition) ? \get_class($definition) : \gettype($definition)
                ));
            }

            $linkList[$name] = $link;
        }

        $this->links = $linkList;
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta(): ?array
    {
        return $this->meta;
    }

    /**
     * {@inheritdoc}
     *
     * @throws SchemaException
     */
    public function setMeta(array $meta): void
    {
        $this->assertMeta($meta);

        $this->meta = $meta;
    }

    /**
     * Assert meta data is correct.
     *
     * @param mixed[] $meta
     *
     * @throws SchemaException
     */
    private function assertMeta(array $meta): void
    {
        $keys = \array_keys($meta);
        \array_walk(
            $keys,
            function ($key) {
                if (!\is_string($key)) {
                    throw new SchemaException('Metadata keys must be all strings');
                }
            }
        );
    }
}
