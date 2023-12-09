<?php

/*
 * (c) 2018-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Tests\Mapping\Driver;

use Doctrine\Common\Annotations\AnnotationReader;
use Jgut\JsonApi\Mapping\Driver\AnnotationDriver;
use Jgut\Mapping\Exception\DriverException;

/**
 * @internal
 */
class AnnotationDriverTest extends AbstractDriverTestCase
{
    protected AnnotationReader $reader;

    protected function setUp(): void
    {
        $this->reader = new AnnotationReader();
    }

    public function testNoIdResource(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessageMatches('/^Resource ".+" does not define an identifier\.$/');

        $driver = new AnnotationDriver(
            [
                __DIR__ . '/../Files/Classes/Invalid/Annotation/NoIdResource.php',
            ],
            $this->reader,
        );

        $driver->getMetadata();
    }

    public function testMultipleIdResource(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessageMatches('/^Resource ".+" cannot define more than one identifier\.$/');

        $driver = new AnnotationDriver(
            [
                __DIR__ . '/../Files/Classes/Invalid/Annotation/MultipleIdResource.php',
            ],
            $this->reader,
        );

        $driver->getMetadata();
    }

    public function testAnnotationResources(): void
    {
        $driver = new AnnotationDriver(
            [
                __DIR__ . '/../Files/Classes/Valid/Annotation',
            ],
            $this->reader,
        );

        $this->checkResources($driver);
    }
}
