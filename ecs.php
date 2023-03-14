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

use Jgut\ECS\Config\ConfigSet74;
use PhpCsFixerCustomFixers\Fixer\NoNullableBooleanTypeFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

$header = <<<'HEADER'
json-api (https://github.com/juliangut/json-api).
PSR-7 aware json-api integration.

@license BSD-3-Clause
@link https://github.com/juliangut/json-api
@author Julián Gutiérrez <juliangut@gmail.com>
HEADER;

return static function (ECSConfig $ecsConfig) use ($header): void {
    $ecsConfig->paths([
        __FILE__,
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $skips = [
        NoNullableBooleanTypeFixer::class => [
            __DIR__ . '/src/Mapping/Annotation/LinkTrait.php',
            __DIR__ . '/src/Mapping/Metadata/LinkTrait.php',
        ],
    ];

    if (\PHP_VERSION_ID < 80_000) {
        $skips = array_merge(
            $skips,
            [
                __DIR__ . '/tests/JsonApi/Mapping/Files/Classes/Invalid/Attribute',
                __DIR__ . '/tests/JsonApi/Mapping/Files/Classes/Valid/Attribute',
            ],
        );
    }

    (new ConfigSet74())
        ->setHeader($header)
        ->enablePhpUnitRules()
        ->setAdditionalSkips($skips)
        ->configure($ecsConfig);
};
