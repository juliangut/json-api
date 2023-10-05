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

namespace Jgut\JsonApi\PhpStan;

use Jgut\JsonApi\Schema\MetadataSchema;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\MethodTypeSpecifyingExtension;
use PHPStan\Type\ObjectWithoutClassType;

class AssertResourceType implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
    private TypeSpecifier $typeSpecifier;

    public function getClass(): string
    {
        return MetadataSchema::class;
    }

    public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
    {
        $this->typeSpecifier = $typeSpecifier;
    }

    public function isMethodSupported(
        MethodReflection $methodReflection,
        MethodCall $node,
        TypeSpecifierContext $context,
    ): bool {
        return $methodReflection->getName() === 'assertResourceType'
            && $context->null()
            && \array_key_exists(0, $node->getArgs());
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function specifyTypes(
        MethodReflection $methodReflection,
        MethodCall $node,
        Scope $scope,
        TypeSpecifierContext $context,
    ): SpecifiedTypes {
        $resource = $node->getArgs()[0];

        return $this->typeSpecifier->create(
            $resource->value,
            new ObjectWithoutClassType(),
            TypeSpecifierContext::createTruthy(),
        );
    }
}
