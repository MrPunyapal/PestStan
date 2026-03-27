<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ExpressionTypeResolverExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPUnit\Framework\TestCase;

/**
 * Resolves dynamic property types on TestCase from beforeEach/beforeAll hook assignments.
 */
final class TestCaseDynamicPropertyTypeExtension implements ExpressionTypeResolverExtension
{
    public function __construct(
        private readonly PestHookPropertyReader $hookPropertyReader,
    ) {}

    public function getType(Expr $expr, Scope $scope): ?Type
    {
        if (! $expr instanceof PropertyFetch) {
            return null;
        }

        if (! $expr->name instanceof Identifier) {
            return null;
        }

        $varType = $scope->getType($expr->var);
        $testCaseType = new ObjectType(TestCase::class);

        if (! $testCaseType->isSuperTypeOf($varType)->yes()) {
            return null;
        }

        $propertyName = $expr->name->name;

        if ($this->hasNativeProperty($varType, $propertyName)) {
            return null;
        }

        $propertyTypes = $this->hookPropertyReader->getPropertyTypes($scope->getFile());

        return $propertyTypes[$propertyName] ?? null;
    }

    /**
     * Checks whether the type has a natively declared property.
     */
    private function hasNativeProperty(Type $type, string $propertyName): bool
    {
        foreach ($type->getObjectClassReflections() as $classReflection) {
            if ($classReflection->hasNativeProperty($propertyName)) {
                return true;
            }
        }

        return false;
    }
}
