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

use Jgut\JsonApi\Mapping\Annotation\Relationship;
use PHPUnit\Framework\TestCase;

/**
 * Relationship annotation tests.
 */
class RelationshipTest extends TestCase
{
    /**
     * @var Relationship
     */
    protected $annotation;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->annotation = new Relationship([]);
    }

    public function testDefaults(): void
    {
        self::assertNull($this->annotation->isSelfLinkIncluded());
        self::assertNull($this->annotation->isRelatedLinkIncluded());
        self::assertEmpty($this->annotation->getLinks());
    }

    public function testSelfLinkIncluded(): void
    {
        $this->annotation->setSelfLinkIncluded(true);

        self::assertTrue($this->annotation->isSelfLinkIncluded());
    }

    public function testRelatedLinkIncluded(): void
    {
        $this->annotation->setRelatedLinkIncluded(true);

        self::assertTrue($this->annotation->isRelatedLinkIncluded());
    }

    public function testLinks(): void
    {
        $this->annotation->setLinks(['custom' => '/custom']);

        self::assertEquals(['custom' => '/custom'], $this->annotation->getLinks());
    }
}
