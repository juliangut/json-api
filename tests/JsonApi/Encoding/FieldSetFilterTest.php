<?php

/*
 * (c) 2018-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Tests\Encoding;

use Closure;
use Jgut\JsonApi\Encoding\FieldSetFilter;
use Neomerx\JsonApi\Contracts\Parser\ResourceInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class FieldSetFilterTest extends TestCase
{
    public function testNoFilter(): void
    {
        $callableOne = new class () {
            public function __invoke(): string
            {
                return 'aaa';
            }
        };
        $callableTwo = new class () {
            public function __invoke(): string
            {
                return 'aaa';
            }
        };
        $attributes = [
            'attributeOne' => Closure::fromCallable($callableOne),
            'attributeTwo' => Closure::fromCallable($callableTwo),
        ];

        $resource = $this->getMockBuilder(ResourceInterface::class)
            ->getMock();
        $resource->expects(static::once())
            ->method('getType')
            ->willReturn('mock');
        $resource->expects(static::once())
            ->method('getAttributes')
            ->willReturn($attributes);

        $filter = new FieldSetFilter([]);

        foreach ($filter->getAttributes($resource) as $attribute) {
            static::assertEquals('aaa', $attribute);
        }
    }

    public function testFilter(): void
    {
        $callableOne = new class () {
            public function __invoke(): string
            {
                return 'aaa';
            }
        };
        $callableTwo = new class () {
            public function __invoke(): string
            {
                return 'bbb';
            }
        };
        $attributes = [
            'attributeOne' => Closure::fromCallable($callableOne),
            'attributeTwo' => Closure::fromCallable($callableTwo),
        ];

        $resource = $this->getMockBuilder(ResourceInterface::class)
            ->getMock();
        $resource->expects(static::once())
            ->method('getType')
            ->willReturn('mock');
        $resource->expects(static::once())
            ->method('getAttributes')
            ->willReturn($attributes);

        $filter = new FieldSetFilter(['mock' => ['attributeOne']]);

        foreach ($filter->getAttributes($resource) as $attribute) {
            static::assertEquals('aaa', $attribute);
        }
    }
}
