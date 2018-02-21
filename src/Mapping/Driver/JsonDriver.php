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

namespace Jgut\JsonApi\Mapping\Driver;

use Jgut\Mapping\Driver\AbstractMappingDriver;
use Jgut\Mapping\Driver\Traits\JsonMappingTrait;

/**
 * JSON mapping driver.
 */
class JsonDriver extends AbstractMappingDriver implements DriverInterface
{
    use JsonMappingTrait;
    use MappingTrait;
}
