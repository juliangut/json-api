<?php

/*
 * (c) 2018-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Encoding;

use Jgut\JsonApi\Exception\SchemaException;
use Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use Neomerx\JsonApi\Encoder\Encoder;

class Options implements OptionsInterface
{
    public const JSON_ENCODE_OPTIONS = \JSON_UNESCAPED_UNICODE
        | \JSON_UNESCAPED_SLASHES
        | \JSON_PRESERVE_ZERO_FRACTION
        | \JSON_HEX_AMP
        | \JSON_HEX_APOS
        | \JSON_HEX_QUOT
        | \JSON_HEX_TAG;

    private int $encodeOptions = self::JSON_ENCODE_OPTIONS;

    private int $encodeDepth = Encoder::DEFAULT_JSON_ENCODE_DEPTH;

    /**
     * @var non-empty-string|null
     */
    private ?string $group = null;

    /**
     * @var array<mixed>|null
     */
    private ?array $links = null;

    /**
     * @var array<non-empty-string, mixed>|null
     */
    private ?array $meta = null;

    public function getEncodeOptions(): int
    {
        return $this->encodeOptions;
    }

    public function setEncodeOptions(int $encodingOptions): void
    {
        $this->encodeOptions = $encodingOptions;
    }

    public function getEncodeDepth(): int
    {
        return $this->encodeDepth;
    }

    public function setEncodeDepth(int $encodingDepth): void
    {
        $this->encodeDepth = $encodingDepth;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function setGroup(string $group): void
    {
        $this->group = $group;
    }

    public function getLinks(): ?array
    {
        return $this->links;
    }

    /**
     * @throws SchemaException
     */
    public function setLinks(array $links): void
    {
        if ($links !== [] && array_is_list($links)) {
            throw new SchemaException('Links keys must be all strings.');
        }

        $linkList = [];
        foreach ($links as $name => $definition) {
            if ($definition instanceof LinkInterface) {
                $link = $definition;
            } elseif (\is_array($definition)) {
                $href = $definition['href'];
                if (!$href instanceof LinkInterface) {
                    throw new SchemaException(sprintf(
                        'Link href must be an instance of %s or array, %s given.',
                        LinkInterface::class,
                        \is_object($href) ? $href::class : \gettype($href),
                    ));
                }
                $this->assertMeta($definition['meta']);

                $link = [
                    'href' => $href,
                    'meta' => $definition['meta'],
                ];
            } else {
                throw new SchemaException(sprintf(
                    'Link must be an instance of %s or array, "%s" given.',
                    LinkInterface::class,
                    \is_object($definition) ? $definition::class : \gettype($definition),
                ));
            }

            $linkList[$name] = $link;
        }

        $this->links = $linkList;
    }

    public function getMeta(): ?array
    {
        return $this->meta;
    }

    /**
     * @throws SchemaException
     */
    public function setMeta(array $meta): void
    {
        $this->assertMeta($meta);

        $this->meta = $meta;
    }

    /**
     * @param mixed|array<mixed> $meta
     *
     * @throws SchemaException
     */
    private function assertMeta($meta): void
    {
        if (!\is_array($meta)) {
            throw new SchemaException('Metadata must be an array.');
        }

        $keys = array_keys($meta);
        array_walk(
            $keys,
            static function ($key): void {
                if (!\is_string($key)) {
                    throw new SchemaException('Metadata keys must be all strings.');
                }
            },
        );
    }
}
