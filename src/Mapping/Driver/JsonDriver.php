<?php

/*
 * (c) 2018-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Mapping\Driver;

use Jgut\Mapping\Driver\AbstractMappingJsonDriver;

final class JsonDriver extends AbstractMappingJsonDriver implements DriverInterface
{
    use FileMappingTrait;
}
