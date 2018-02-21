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

use Jgut\JsonApi\Tests\Stubs\AbstractMetadataStub;
use PHPUnit\Framework\TestCase;

/**
 * Abstract metadata tests.
 */
class AbstractMetadataTest extends TestCase
{
    public function testCreation()
    {
        $metadata = new AbstractMetadataStub('Class', 'Name');

        self::assertEquals('Class', $metadata->getClass());
        self::assertEquals('Name', $metadata->getName());
    }
}
