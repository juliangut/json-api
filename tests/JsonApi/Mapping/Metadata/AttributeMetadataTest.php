<?php

/*
 * (c) 2018-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Tests\Mapping\Metadata;

use Jgut\JsonApi\Mapping\Metadata\AttributeMetadata;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class AttributeMetadataTest extends TestCase
{
    protected AttributeMetadata $attribute;

    protected function setUp(): void
    {
        $this->attribute = new AttributeMetadata('Class', 'Name');
    }

    public function testDefaults(): void
    {
        static::assertNull($this->attribute->getGetter());
        static::assertNull($this->attribute->getSetter());
        static::assertEquals([], $this->attribute->getGroups());
    }

    public function testGetter(): void
    {
        $this->attribute->setGetter('getAttr');

        static::assertEquals('getAttr', $this->attribute->getGetter());
    }

    public function testSetter(): void
    {
        $this->attribute->setSetter('setAttr');

        static::assertEquals('setAttr', $this->attribute->getSetter());
    }

    public function testGroups(): void
    {
        $this->attribute->setGroups(['one', 'two']);

        static::assertEquals(['one', 'two'], $this->attribute->getGroups());
    }
}
