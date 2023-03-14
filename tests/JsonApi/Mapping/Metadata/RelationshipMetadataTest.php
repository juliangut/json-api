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
 * @internal
 */
class RelationshipMetadataTest extends TestCase
{
    protected RelationshipMetadata $relationship;

    protected function setUp(): void
    {
        $this->relationship = new RelationshipMetadata('Class', 'Name');
    }

    public function testDefaults(): void
    {
        static::assertNull($this->relationship->isLinkSelf());
        static::assertNull($this->relationship->isLinkRelated());
        static::assertEmpty($this->relationship->getLinks());
        static::assertEmpty($this->relationship->getMeta());
    }

    public function testSelfLinkIncluded(): void
    {
        $this->relationship->setLinkSelf(true);

        static::assertTrue($this->relationship->isLinkSelf());
    }

    public function testRelatedLinkIncluded(): void
    {
        $this->relationship->setLinkRelated(true);

        static::assertTrue($this->relationship->isLinkRelated());
    }

    public function testLinks(): void
    {
        $link = new LinkMetadata('/custom', 'custom');

        $this->relationship->addLink($link);

        static::assertEquals(['custom' => $link], $this->relationship->getLinks());
    }
}
