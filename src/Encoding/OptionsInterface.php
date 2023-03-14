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

interface OptionsInterface
{
    /**
     * Get JSON encode options.
     */
    public function getEncodeOptions(): int;

    /**
     * Set JSON encode options.
     */
    public function setEncodeOptions(int $encodingOptions): void;

    /**
     * Get JSON encode depth.
     */
    public function getEncodeDepth(): int;

    /**
     * Set JSON encode depth.
     */
    public function setEncodeDepth(int $encodingDepth): void;

    /**
     * @return non-empty-string|null
     */
    public function getGroup(): ?string;

    /**
     * @param non-empty-string $group
     */
    public function setGroup(string $group): void;

    /**
     * @return array<mixed>|null
     */
    public function getLinks(): ?array;

    /**
     * @param array<mixed> $links
     */
    public function setLinks(array $links): void;

    /**
     * @return array<non-empty-string, mixed>|null
     */
    public function getMeta(): ?array;

    /**
     * @param array<non-empty-string, mixed> $meta
     */
    public function setMeta(array $meta): void;
}
