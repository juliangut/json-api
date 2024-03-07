<?php

/*
 * (c) 2018-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Mapping\Driver;

use Jgut\JsonApi\Mapping\Metadata\LinkMetadata;
use Jgut\Mapping\Exception\DriverException;

trait LinksTrait
{
    /**
     * @param LinkMapping $links
     *
     * @throws DriverException
     *
     * @return array<LinkMetadata>
     */
    private function getLinksMetadata(array $links): array
    {
        $linkList = [];
        foreach ($links as $title => $linkDefinition) {
            if (\is_string($linkDefinition)) {
                $link = new LinkMetadata($linkDefinition, $title);
            } elseif (\is_array($linkDefinition)) {
                /** @var array{href: string, meta?: array<string, mixed>} $linkDefinition */
                $link = new LinkMetadata($linkDefinition['href'], $title);

                if (\array_key_exists('meta', $linkDefinition)) {
                    $link->setMeta($linkDefinition['meta']);
                }
            } else {
                throw new DriverException(sprintf(
                    'Link definition must be either a string or array, %s given.',
                    \is_object($linkDefinition) ? $linkDefinition::class : \gettype($linkDefinition),
                ));
            }

            $linkList[$title] = $link;
        }

        return $linkList;
    }
}
