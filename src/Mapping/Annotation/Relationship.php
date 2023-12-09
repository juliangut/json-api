<?php

/*
 * (c) 2018-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Mapping\Annotation;

/**
 * @Annotation
 *
 * @Target("PROPERTY")
 */
final class Relationship extends AbstractField
{
    use GroupTrait;
    use LinkTrait;
    use MetaTrait;
}
