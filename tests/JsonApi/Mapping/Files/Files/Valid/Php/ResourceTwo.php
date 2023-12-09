<?php

/*
 * (c) 2018-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Tests\Mapping\Files\Files\Valid\Php;

use Jgut\JsonApi\Tests\Mapping\Files\Classes\Valid\Attribute\ResourceTwo;

return [
    [
        'class' => ResourceTwo::class,
        'linkRelated' => false,
        'links' => [
            'me' => '/me',
            'you' => '/you',
        ],
        'identifier' => 'uuid',
        'attributes' => [
            [
                'property' => 'two',
                'groups' => ['read'],
            ],
        ],
    ],
];
