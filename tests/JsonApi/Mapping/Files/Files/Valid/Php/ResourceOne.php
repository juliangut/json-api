<?php

/*
 * (c) 2018-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Tests\Mapping\Files\Files\Valid\Php;

use Jgut\JsonApi\Schema\MetadataSchema;

return [
    [
        'class' => 'Jgut\JsonApi\Tests\Mapping\Files\Classes\Valid\Annotation\ResourceOne',
        'name' => 'resourceA',
        'schema' => MetadataSchema::class,
        'prefix' => 'resource',
        'linkSelf' => true,
        'meta' => [
            'first' => 'firstValue',
            'second' => 'secondValue',
        ],
        'identifier' => [
            'property' => 'uuid',
            'name' => 'id',
            'getter' => 'getId',
            'setter' => 'setId',
        ],
        'attributes' => [
            [
                'property' => 'one',
                'name' => 'theOne',
            ],
        ],
        'relationships' => [
            [
                'class' => 'Jgut\JsonApi\Tests\Mapping\Files\Classes\Valid\Annotation\ResourceTwo',
                'property' => 'relative',
                'links' => [
                    'custom' => [
                        'href' => '/custom/path',
                        'meta' => [
                            'key' => 'path',
                        ],
                    ],
                ],
                'meta' => ['key' => 'value'],
            ],
        ],
    ],
];
