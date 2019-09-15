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

namespace Jgut\JsonApi\Tests\Encoding;

use Jgut\JsonApi\Encoding\Options;
use Jgut\JsonApi\Exception\SchemaException;
use Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use Neomerx\JsonApi\Schema\Link;
use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase
{
    /**
     * @var Options
     */
    protected $options;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->options = new Options();
    }

    public function testDefaults(): void
    {
        $defaultEncodeOptions = \JSON_UNESCAPED_UNICODE
            | \JSON_UNESCAPED_SLASHES
            | \JSON_PRESERVE_ZERO_FRACTION
            | \JSON_HEX_AMP
            | \JSON_HEX_APOS
            | \JSON_HEX_QUOT
            | \JSON_HEX_TAG;

        self::assertEquals($defaultEncodeOptions, $this->options->getEncodeOptions());
        self::assertEquals(512, $this->options->getEncodeDepth());
        self::assertNull($this->options->getGroup());
        self::assertNull($this->options->getLinks());
        self::assertNull($this->options->getMeta());
    }

    public function testSetEncodeOptions(): void
    {
        $this->options->setEncodeOptions(\JSON_PRESERVE_ZERO_FRACTION);

        self::assertEquals(\JSON_PRESERVE_ZERO_FRACTION, $this->options->getEncodeOptions());
    }

    public function testSetEncodeDepth(): void
    {
        $this->options->setEncodeDepth(100);

        self::assertEquals(100, $this->options->getEncodeDepth());
    }

    public function testSetGroup(): void
    {
        $this->options->setGroup('group');

        self::assertEquals('group', $this->options->getGroup());
    }

    public function testInvalidLinkName(): void
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionMessage('Links keys must be all strings');

        $this->options->setLinks(['numericKey']);
    }

    public function testInvalidLinkType(): void
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionMessage('Link must be an instance of ' . LinkInterface::class . ', string given');

        $this->options->setLinks(['first' => 'invalid']);
    }

    public function testSetLinks(): void
    {
        $links = [
            'first' => new Link(false, '', false),
        ];

        $this->options->setLinks($links);

        self::assertEquals($links, $this->options->getLinks());
    }

    public function testInvalidMetadataKeys(): void
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionMessage('Metadata keys must be all strings');

        $this->options->setMeta(['meta' => 'value', 'anotherValue']);
    }

    public function testSetMeta(): void
    {
        $this->options->setMeta(['meta' => 'value']);

        self::assertEquals(['meta' => 'value'], $this->options->getMeta());
    }
}
