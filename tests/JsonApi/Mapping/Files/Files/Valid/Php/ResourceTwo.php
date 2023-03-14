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
