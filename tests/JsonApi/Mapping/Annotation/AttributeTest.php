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

namespace Jgut\JsonApi\Tests\Mapping\Annotation;

use Jgut\JsonApi\Mapping\Annotation\Attribute;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class AttributeTest extends TestCase
{
    public function testDefaults(): void
    {
        $annotation = new Attribute([]);

        static::assertNull($annotation->getName());
        static::assertNull($annotation->getGetter());
        static::assertNull($annotation->getSetter());
        static::assertEquals([], $annotation->getGroups());
    }
}
