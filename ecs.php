<?php

/*
 * (c) 2018-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

use Jgut\ECS\Config\ConfigSet80;
use PhpCsFixerCustomFixers\Fixer\NoNullableBooleanTypeFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $header = <<<'HEADER'
    (c) 2018-{{year}} Julián Gutiérrez <juliangut@gmail.com>

    @license BSD-3-Clause
    @link https://github.com/juliangut/json-api
    HEADER;

    $ecsConfig->paths([
        __FILE__,
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);
    $ecsConfig->cacheDirectory('.ecs.cache');

    (new ConfigSet80())
        ->setHeader($header)
        ->enablePhpUnitRules()
        ->setAdditionalSkips([
            NoNullableBooleanTypeFixer::class => [
                __DIR__ . '/src/Mapping/Annotation/LinkTrait.php',
                __DIR__ . '/src/Mapping/Metadata/LinkTrait.php',
            ],
        ])
        ->configure($ecsConfig);
};
