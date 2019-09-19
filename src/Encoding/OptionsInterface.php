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

/**
 * Encoding options interface.
 */
interface OptionsInterface
{
    /**
     * Get JSON encode options.
     *
     * @return int
     */
    public function getEncodeOptions(): int;

    /**
     * Set JSON encode options.
     *
     * @param int $encodingOptions
     */
    public function setEncodeOptions(int $encodingOptions): void;

    /**
     * Get JSON encode depth.
     *
     * @return int
     */
    public function getEncodeDepth(): int;

    /**
     * Set JSON encode depth.
     *
     * @param int $encodingDepth
     */
    public function setEncodeDepth(int $encodingDepth): void;

    /**
     * @return string|null
     */
    public function getGroup(): ?string;

    /**
     * @param string $group
     */
    public function setGroup(string $group): void;

    /**
     * @return mixed[]|null
     */
    public function getLinks(): ?array;

    /**
     * @param mixed[] $links
     */
    public function setLinks(array $links): void;

    /**
     * @return string[]|null
     */
    public function getMeta(): ?array;

    /**
     * @param mixed[] $meta
     */
    public function setMeta(array $meta): void;
}
