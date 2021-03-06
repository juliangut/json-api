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

namespace Jgut\JsonApi\Tests\Stubs;

use Jgut\JsonApi\Encoding\Encoder;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;

/**
 * Custom encoder stub.
 */
class EncoderStub extends Encoder
{
    /**
     * @return FactoryInterface
     */
    public static function doCreateFactory(): FactoryInterface
    {
        return static::createFactory();
    }
}
