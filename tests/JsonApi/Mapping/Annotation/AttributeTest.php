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

namespace Jgut\Slim\Routing\Tests\Mapping\Annotation;

use Jgut\JsonApi\Mapping\Annotation\Attribute;
use PHPUnit\Framework\TestCase;

/**
 * Attribute annotation tests.
 */
class AttributeTest extends TestCase
{
    /**
     * @var Attribute
     */
    protected $annotation;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->annotation = new Attribute([]);
    }

    public function testDefaults()
    {
        self::assertNull($this->annotation->getName());
        self::assertNull($this->annotation->getGetter());
        self::assertNull($this->annotation->getSetter());
        self::assertEquals([], $this->annotation->getGroups());
    }

    public function testName()
    {
        $this->annotation->setName('name');

        self::assertEquals('name', $this->annotation->getName());
    }

    public function testGetter()
    {
        $this->annotation->setGetter('setGetter');

        self::assertEquals('setGetter', $this->annotation->getGetter());
    }

    public function testSetter()
    {
        $this->annotation->setSetter('setSetter');

        self::assertEquals('setSetter', $this->annotation->getSetter());
    }

    public function testGroups()
    {
        $this->annotation->setGroups(['one', 'two']);

        self::assertEquals(['one', 'two'], $this->annotation->getGroups());
    }
}
