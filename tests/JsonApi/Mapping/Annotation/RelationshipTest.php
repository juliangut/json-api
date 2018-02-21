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
    protected function setUp()
    {
        $this->annotation = new Relationship([]);
    }

    public function testDefaults()
    {
        self::assertFalse($this->annotation->isIncluded());
        self::assertFalse($this->annotation->isSelfLinkIncluded());
        self::assertFalse($this->annotation->isRelatedLinkIncluded());
    }

    public function testDefaultIncluded()
    {
        $this->annotation->setIncluded(true);

        self::assertTrue($this->annotation->isIncluded());
    }

    public function testSelfLinkIncluded()
    {
        $this->annotation->setSelfLinkIncluded(true);

        self::assertTrue($this->annotation->isSelfLinkIncluded());
    }

    public function testRelatedLinkIncluded()
    {
        $this->annotation->setRelatedLinkIncluded(true);

        self::assertTrue($this->annotation->isRelatedLinkIncluded());
    }
}
