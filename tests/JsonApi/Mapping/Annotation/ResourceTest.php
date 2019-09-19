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
    protected function setUp(): void
    {
        $this->annotation = new AnnotationResource([]);
    }

    public function testDefaults(): void
    {
        self::assertNull($this->annotation->getName());
        self::assertNull($this->annotation->getSchemaClass());
    }

    public function testName(): void
    {
        $this->annotation->setName('name');

        self::assertEquals('name', $this->annotation->getName());
    }

    public function testSchemaClass(): void
    {
        $this->annotation->setSchemaClass('Class');

        self::assertEquals('Class', $this->annotation->getSchemaClass());
    }

    public function testUrlPrefix(): void
    {
        $this->annotation->setUrlPrefix('/resource');

        self::assertEquals('/resource', $this->annotation->getUrlPrefix());
    }

    public function testLinksIncluded(): void
    {
        $this->annotation->setSelfLinkIncluded(true);
        $this->annotation->setRelatedLinkIncluded(false);

        self::assertTrue($this->annotation->isSelfLinkIncluded());
        self::assertFalse($this->annotation->isRelatedLinkIncluded());
    }
}
