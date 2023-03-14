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
