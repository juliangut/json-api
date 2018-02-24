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

use Jgut\JsonApi\Mapping\Annotation\Resource as AnnotationResource;
use PHPUnit\Framework\TestCase;

/**
 * Resource annotation tests.
 */
class ResourceTest extends TestCase
{
    /**
     * @var AnnotationResource
     */
    protected $annotation;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->annotation = new AnnotationResource([]);
    }

    public function testDefaults()
    {
        self::assertNull($this->annotation->getName());
        self::assertNull($this->annotation->getSchemaClass());
        self::assertTrue($this->annotation->hasAttributesInInclude());
    }

    public function testName()
    {
        $this->annotation->setName('name');

        self::assertEquals('name', $this->annotation->getName());
    }

    public function testSchemaClass()
    {
        $this->annotation->setSchemaClass('Class');

        self::assertEquals('Class', $this->annotation->getSchemaClass());
    }

    public function testAttributesInInclude()
    {
        $this->annotation->setAttributesInInclude(false);

        self::assertFalse($this->annotation->hasAttributesInInclude());
    }

    public function testUrl()
    {
        $this->annotation->setUrl('/resource');

        self::assertEquals('/resource', $this->annotation->getUrl());
    }
}
