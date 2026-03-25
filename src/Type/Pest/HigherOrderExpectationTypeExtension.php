<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use Pest\Expectation;
use Pest\Expectations\HigherOrderExpectation;
use Pest\Expectations\OppositeExpectation;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ExpressionTypeResolverExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

/**
 * Resolves types for higher order expectation property access and method chaining.
 */
final class HigherOrderExpectationTypeExtension implements ExpressionTypeResolverExtension
{
    /** @var list<string> */
    private const EXPECTATION_KNOWN_PROPERTIES = ['each', 'classes', 'traits', 'interfaces', 'enums'];

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
    ) {}

    public function getType(Expr $expr, Scope $scope): ?Type
    {
        if ($expr instanceof PropertyFetch) {
            return $this->resolvePropertyFetch($expr, $scope);
        }

        if ($expr instanceof MethodCall) {
            return $this->resolveMethodCall($expr, $scope);
        }

        return null;
    }

    private function resolvePropertyFetch(PropertyFetch $expr, Scope $scope): ?Type
    {
        if (! $expr->name instanceof Identifier) {
            return null;
        }

        $propertyName = $expr->name->name;
        $varType = $scope->getType($expr->var);

        $expectationType = new ObjectType(Expectation::class);
        if ($expectationType->isSuperTypeOf($varType)->yes()) {
            return $this->resolveExpectationPropertyFetch($varType, $propertyName, $scope);
        }

        $higherOrderType = new ObjectType(HigherOrderExpectation::class);
        if ($higherOrderType->isSuperTypeOf($varType)->yes()) {
            return $this->resolveHigherOrderPropertyFetch($varType, $propertyName, $scope);
        }

        return null;
    }

    private function resolveExpectationPropertyFetch(Type $varType, string $propertyName, Scope $scope): ?Type
    {
        if ($propertyName === 'not') {
            $valueType = $varType->getTemplateType(Expectation::class, 'TValue');

            return new GenericObjectType(OppositeExpectation::class, [$valueType]);
        }

        if (in_array($propertyName, self::EXPECTATION_KNOWN_PROPERTIES, true)) {
            return null;
        }

        if ($this->isNativeExpectationProperty($propertyName)) {
            return null;
        }

        $valueType = $varType->getTemplateType(Expectation::class, 'TValue');

        $propertyType = $this->resolvePropertyType($valueType, $propertyName, $scope);
        if (! $propertyType instanceof Type) {
            return null;
        }

        return new GenericObjectType(HigherOrderExpectation::class, [
            new GenericObjectType(Expectation::class, [$valueType]),
            $propertyType,
        ]);
    }

    private function resolveHigherOrderPropertyFetch(Type $varType, string $propertyName, Scope $scope): ?Type
    {
        if ($propertyName === 'not') {
            return null;
        }

        $originalType = $varType->getTemplateType(HigherOrderExpectation::class, 'TOriginalValue');
        $currentValueType = $varType->getTemplateType(HigherOrderExpectation::class, 'TValue');

        $propertyType = $this->resolvePropertyType($currentValueType, $propertyName, $scope);
        if (! $propertyType instanceof Type) {
            return null;
        }

        return new GenericObjectType(HigherOrderExpectation::class, [
            $originalType,
            $propertyType,
        ]);
    }

    private function resolveMethodCall(MethodCall $expr, Scope $scope): ?Type
    {
        if (! $expr->name instanceof Identifier) {
            return null;
        }

        $varType = $scope->getType($expr->var);
        $higherOrderType = new ObjectType(HigherOrderExpectation::class);

        if (! $higherOrderType->isSuperTypeOf($varType)->yes()) {
            return null;
        }

        $methodName = $expr->name->name;

        if ($methodName === 'and') {
            return $this->resolveHigherOrderAndCall($expr, $scope);
        }

        if (! Expectation::hasMethod($methodName)) {
            return null;
        }

        $originalType = $varType->getTemplateType(HigherOrderExpectation::class, 'TOriginalValue');

        $originalValueType = $originalType->getTemplateType(Expectation::class, 'TValue');

        return new GenericObjectType(HigherOrderExpectation::class, [
            $originalType,
            $originalValueType,
        ]);
    }

    private function resolveHigherOrderAndCall(MethodCall $expr, Scope $scope): ?Type
    {
        $args = $expr->getArgs();
        if ($args === []) {
            return null;
        }

        $newValueType = $scope->getType($args[0]->value);

        return new GenericObjectType(Expectation::class, [$newValueType]);
    }

    private function resolvePropertyType(Type $objectType, string $propertyName, Scope $scope): ?Type
    {
        if ($objectType->hasProperty($propertyName)->no()) {
            return null;
        }

        return $objectType->getProperty($propertyName, $scope)->getReadableType();
    }

    private function isNativeExpectationProperty(string $propertyName): bool
    {
        if (! $this->reflectionProvider->hasClass(Expectation::class)) {
            return false;
        }

        return $this->reflectionProvider->getClass(Expectation::class)
            ->hasNativeProperty($propertyName);
    }
}
