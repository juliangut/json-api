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

namespace Jgut\JsonApi\Tests\Encoding;

use BadMethodCallException;
use Jgut\JsonApi\Encoding\Encoder;
use Jgut\JsonApi\Encoding\Factory;
use Jgut\JsonApi\Tests\Stubs\EncoderStub;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class EncoderTest extends TestCase
{
    public function testCreateFactory(): void
    {
        static::assertInstanceOf(Factory::class, EncoderStub::doCreateFactory());
    }

    public function testRequireInstance(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessageMatches(
            '/Call to Encoder::instance is not allowed\. Use .+::getResourceEncoder instead./',
        );

        Encoder::instance();
    }
}
