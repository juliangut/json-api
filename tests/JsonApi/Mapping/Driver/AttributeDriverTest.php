<?php

/*
 * (c) 2018-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Tests\Mapping\Driver;

use Jgut\JsonApi\Mapping\Driver\AttributeDriver;
use Jgut\Mapping\Exception\DriverException;

/**
 * @internal
 */
class AttributeDriverTest extends AbstractDriverTestCase
{
    protected function setUp(): void
    {
        if (\PHP_VERSION_ID < 80_000) {
            static::markTestSkipped('Named arguments supported from PHP 8.0');
        }
    }

    public function testNoIdResource(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessageMatches('/^Resource ".+" does not define an identifier\.$/');

        $driver = new AttributeDriver([
            __DIR__ . '/../Files/Classes/Invalid/Attribute/NoIdResource.php',
        ]);

        $driver->getMetadata();
    }

    public function testMultipleIdResource(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessageMatches('/^Resource ".+" cannot define more than one identifier\.$/');

        $driver = new AttributeDriver([
            __DIR__ . '/../Files/Classes/Invalid/Attribute/MultipleIdResource.php',
        ]);

        $driver->getMetadata();
    }

    public function testAttributeResources(): void
    {
        $driver = new AttributeDriver([
            __DIR__ . '/../Files/Classes/Valid/Attribute',
        ]);

        $this->checkResources($driver);
    }
}
