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

use Jgut\JsonApi\Mapping\Metadata\LinkMetadata;
use Jgut\JsonApi\Mapping\Metadata\RelationshipMetadata;
use PHPUnit\Framework\TestCase;

/**
 * Relationship metadata tests.
 */
class RelationshipMetadataTest extends TestCase
{
    /**
     * @var RelationshipMetadata
     */
    protected $relationship;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->relationship = new RelationshipMetadata('Class', 'Name');
    }

    public function testDefaults()
    {
        self::assertFalse($this->relationship->isDefaultIncluded());
        self::assertFalse($this->relationship->isSelfLinkIncluded());
        self::assertFalse($this->relationship->isRelatedLinkIncluded());
        self::assertFalse($this->relationship->isRelatedLinkIncluded());
        self::assertEmpty($this->relationship->getLinks());
    }

    public function testDefaultIncluded()
    {
        $this->relationship->setDefaultIncluded(true);

        self::assertTrue($this->relationship->isDefaultIncluded());
    }

    public function testSelfLinkIncluded()
    {
        $this->relationship->setSelfLinkIncluded(true);

        self::assertTrue($this->relationship->isSelfLinkIncluded());
    }

    public function testRelatedLinkIncluded()
    {
        $this->relationship->setRelatedLinkIncluded(true);

        self::assertTrue($this->relationship->isRelatedLinkIncluded());
    }

    public function testLinks()
    {
        $link = new LinkMetadata('custom');

        $this->relationship->addLink($link);

        self::assertEquals(['custom' => $link], $this->relationship->getLinks());
    }
}
