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

use Jgut\JsonApi\Manager;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Encoder\Encoder as BaseEncoder;

/**
 * Custom encoder.
 */
class Encoder extends BaseEncoder
{
    /**
     * {@inheritdoc}
     *
     * @return FactoryInterface
     */
    protected static function createFactory(): FactoryInterface
    {
        return new Factory();
    }

    /**
     * {@inheritdoc}
     *
     * Prevent usage. Use an instance of Manager instead
     *
     * @throws \BadMethodCallException
     *
     * @return EncoderInterface
     */
    public static function instance(array $schemas = []): EncoderInterface
    {
        throw new \BadMethodCallException(
            \sprintf('Call to Encoder::instance is not allowed. Use %s::getResourceEncoder instead', Manager::class)
        );
    }
}
