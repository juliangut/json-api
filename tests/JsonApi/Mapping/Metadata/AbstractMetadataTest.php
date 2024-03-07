<?php

/*
 * (c) 2018-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Tests\Mapping\Metadata;

use Jgut\JsonApi\Tests\Stubs\AbstractFieldMetadataStub;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class AbstractMetadataTest extends TestCase
{
    public function testCreation(): void
    {
        $metadata = new AbstractFieldMetadataStub('Class', 'Name');

        static::assertEquals('Class', $metadata->getClass());
        static::assertEquals('Name', $metadata->getName());
    }
}
