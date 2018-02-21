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

use Jgut\JsonApi\Mapping\Metadata\AttributeMetadata;
use PHPUnit\Framework\TestCase;

/**
 * Attribute metadata tests.
 */
class AttributeMetadataTest extends TestCase
{
    /**
     * @var AttributeMetadata
     */
    protected $attribute;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->attribute = new AttributeMetadata('Class', 'Name');
    }

    public function testDefaults()
    {
        self::assertNull($this->attribute->getGetter());
        self::assertNull($this->attribute->getSetter());
        self::assertEquals([], $this->attribute->getGroups());
    }

    public function testGetter()
    {
        $this->attribute->setGetter('getAttr');

        self::assertEquals('getAttr', $this->attribute->getGetter());
    }

    public function testSetter()
    {
        $this->attribute->setSetter('setAttr');

        self::assertEquals('setAttr', $this->attribute->getSetter());
    }

    public function testGroups()
    {
        $this->attribute->setGroups(['one', 'two']);

        self::assertEquals(['one', 'two'], $this->attribute->getGroups());
    }
}
