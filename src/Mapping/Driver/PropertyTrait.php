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

use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

trait PropertyTrait
{
    private function getDefaultGetterMethod(ReflectionProperty $property): string
    {
        return ($this->isBoolProperty($property) ? 'is' : 'get') . ucfirst($property->name);
    }

    private function getDefaultSetterMethod(ReflectionProperty $property): string
    {
        return 'set' . ucfirst($property->name);
    }

    private function isBoolProperty(ReflectionProperty $property): bool
    {
        $reflectionType = $property->getDeclaringClass()
            ->getProperty($property->getName())
            ->getType();
        if ($reflectionType !== null) {
            if (
                (\PHP_VERSION_ID >= 80_100 && $reflectionType instanceof ReflectionIntersectionType)
                || (\PHP_VERSION_ID >= 80_000 && $reflectionType instanceof ReflectionUnionType)
            ) {
                $types = $reflectionType->getTypes();
            } else {
                $types = [$reflectionType];
            }
            $attributeType = implode(
                '|',
                array_map(
                    static fn(ReflectionNamedType $reflectionType): string => $reflectionType->getName(),
                    $types,
                ),
            );

            return mb_strpos($attributeType, 'bool') !== false;
        }

        $docComment = $property->getDeclaringClass()
            ->getProperty($property->getName())
            ->getDocComment();

        return \is_string($docComment)
            && preg_match('/@var\s+([a-zA-Z]+)(\s|\n)/', $docComment, $matches) === 1
            && \in_array('bool', explode('|', $matches[1]), true);
    }
}
