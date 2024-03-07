<?php

/*
 * (c) 2018-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Mapping\Driver;

use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
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
            return \in_array('bool', $this->extractTypes($reflectionType), true);
        }

        $docComment = $property->getDeclaringClass()
            ->getProperty($property->getName())
            ->getDocComment();

        return \is_string($docComment)
            && preg_match('/@var\s+([a-zA-Z]+)(\s|\n)/', $docComment, $matches) === 1
            && \in_array('bool', explode('|', $matches[1]), true);
    }

    /**
     * @return list<string>
     */
    private function extractTypes(ReflectionType $reflectionType): array
    {
        if ($reflectionType instanceof ReflectionNamedType) {
            return [$reflectionType->getName()];
        }

        if ((\PHP_VERSION_ID >= 80_100 && $reflectionType instanceof ReflectionIntersectionType)
            || ($reflectionType instanceof ReflectionUnionType)
        ) {
            $types = [];
            foreach ($reflectionType->getTypes() as $type) {
                array_push($types, ...$this->extractTypes($type));
            }

            return array_values($types);
        }

        return [];
    }
}
