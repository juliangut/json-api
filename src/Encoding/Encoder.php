<?php

/*
 * (c) 2018-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Encoding;

use BadMethodCallException;
use Jgut\JsonApi\Manager;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Encoder\Encoder as BaseEncoder;

class Encoder extends BaseEncoder
{
    protected static function createFactory(): FactoryInterface
    {
        return new Factory();
    }

    /**
     * Prevent usage. Use an instance of Manager instead.
     *
     * @param array<mixed> $schemas
     *
     * @throws BadMethodCallException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function instance(array $schemas = []): EncoderInterface
    {
        throw new BadMethodCallException(
            sprintf('Call to Encoder::instance is not allowed. Use %s::getResourceEncoder instead.', Manager::class),
        );
    }
}
