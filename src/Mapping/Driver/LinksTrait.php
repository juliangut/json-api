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
                $link = new LinkMetadata(
                    $linkDefinition['href'],
                    $title,
                    \array_key_exists('meta', $linkDefinition) ? $linkDefinition['meta'] : [],
                );
            } else {
                throw new DriverException(sprintf(
                    'Link definition must be either a string or array, %s given.',
                    \is_object($linkDefinition) ? \get_class($linkDefinition) : \gettype($linkDefinition),
                ));
            }

            $linkList[$title] = $link;
        }

        return $linkList;
    }
}
