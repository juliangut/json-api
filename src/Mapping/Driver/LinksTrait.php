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

/**
 * Links definition trait.
 */
trait LinksTrait
{
    /**
     * Get links metadata.
     *
     * @param mixed[] $links
     *
     * @return LinkMetadata[]
     */
    protected function getLinksMetadata(array $links): array
    {
        if (\count($links) !== 0 && \array_keys($links) === \range(0, \count($links) - 1)) {
            throw new DriverException('Links keys must be all strings');
        }

        $linkList = [];
        foreach ($links as $name => $definition) {
            if (\is_string($definition)) {
                $link = new LinkMetadata($name, $definition);
            } elseif (\is_array($definition)) {
                $link = new LinkMetadata($name, $definition['href']);

                if (isset($definition['meta'])) {
                    $link->setMeta($definition['meta']);
                }
            } else {
                throw new DriverException(\sprintf(
                    'Link definition must be either a string or array, %s given',
                    \is_object($definition) ? \get_class($definition) : \gettype($definition)
                ));
            }

            $linkList[$name] = $link;
        }

        return $linkList;
    }
}
