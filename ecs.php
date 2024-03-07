<?php

/*
 * (c) 2018-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

use Jgut\ECS\Config\ConfigSet80;
use PedroTroller\CS\Fixer\CodingStyle\LineBreakBetweenMethodArgumentsFixer;
use PhpCsFixerCustomFixers\Fixer\NoNullableBooleanTypeFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

$skipRules = [
    NoNullableBooleanTypeFixer::class => [
        __DIR__ . '/src/Mapping/Annotation/LinkTrait.php',
        __DIR__ . '/src/Mapping/Metadata/LinkTrait.php',
    ],
];
if (\PHP_VERSION_ID < 80_100) {
    $skipRules[LineBreakBetweenMethodArgumentsFixer::class] = [
        __DIR__ . '/src/Mapping/Attribute/Attribute.php',
        __DIR__ . '/src/Mapping/Attribute/Identifier.php',
        __DIR__ . '/src/Mapping/Attribute/Relationship.php',
    ];
}

$configSet = (new ConfigSet80())
    ->setHeader(<<<'HEADER'
    (c) 2018-{{year}} Julián Gutiérrez <juliangut@gmail.com>

    @license BSD-3-Clause
    @link https://github.com/juliangut/json-api
    HEADER)
    ->enablePhpUnitRules()
    ->setAdditionalSkips($skipRules);
$paths = [
    __FILE__,
    __DIR__ . '/src',
    __DIR__ . '/tests',
];

if (!method_exists(ECSConfig::class, 'configure')) {
    return static function (ECSConfig $ecsConfig) use ($configSet, $paths): void {
        $ecsConfig->paths($paths);

        $configSet->configure($ecsConfig);
    };
}

return $configSet
    ->configureBuilder()
    ->withPaths($paths);
