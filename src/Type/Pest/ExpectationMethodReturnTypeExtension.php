<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use Pest\Expectation;
use Pest\Mixins\Expectation as MixinsExpectation;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Accessory\AccessoryArrayListType;
use PHPStan\Type\Accessory\AccessoryNumericStringType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\FloatType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\ResourceType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;

/**
 * Fixes return types for assertion methods on Pest\Mixins\Expectation.
 */
final class ExpectationMethodReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return Expectation::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getDeclaringClass()->getName() === MixinsExpectation::class;
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): Type {
        $methodName = $methodReflection->getName();

        if ($methodName === 'toBeInstanceOf') {
            return $this->resolveToBeInstanceOf($methodCall, $scope);
        }

        $narrowedType = $this->getNarrowedType($methodName);
        if ($narrowedType instanceof Type) {
            return new GenericObjectType(Expectation::class, [$narrowedType]);
        }

        $valueType = $scope->getType($methodCall->var)
            ->getTemplateType(Expectation::class, 'TValue');

        return new GenericObjectType(Expectation::class, [$valueType]);
    }

    private function getNarrowedType(string $methodName): ?Type
    {
        return match ($methodName) {
            'toBeString' => new StringType(),
            'toBeInt' => new IntegerType(),
            'toBeFloat' => new FloatType(),
            'toBeBool' => new BooleanType(),
            'toBeTrue' => new ConstantBooleanType(true),
            'toBeFalse' => new ConstantBooleanType(false),
            'toBeNull' => new NullType(),
            'toBeObject' => new ObjectWithoutClassType(),
            'toBeCallable' => new CallableType(),
            'toBeResource' => new ResourceType(),
            'toBeArray' => new ArrayType(
                new UnionType([new IntegerType(), new StringType()]),
                new MixedType(),
            ),
            'toBeList' => TypeCombinator::intersect(
                new ArrayType(new IntegerType(), new MixedType()),
                new AccessoryArrayListType(),
            ),
            'toBeIterable' => new IterableType(new MixedType(), new MixedType()),
            'toBeNumeric' => new UnionType([
                new FloatType(),
                new IntegerType(),
                new IntersectionType([
                    new StringType(),
                    new AccessoryNumericStringType(),
                ]),
            ]),
            'toBeScalar' => new UnionType([
                new BooleanType(),
                new FloatType(),
                new IntegerType(),
                new StringType(),
            ]),
            default => null,
        };
    }

    private function resolveToBeInstanceOf(MethodCall $methodCall, Scope $scope): Type
    {
        $args = $methodCall->getArgs();

        if ($args === []) {
            return new GenericObjectType(Expectation::class, [new ObjectWithoutClassType()]);
        }

        $classType = $scope->getType($args[0]->value);
        $classNames = $classType->getConstantStrings();

        if ($classNames !== []) {
            $objectTypes = array_map(
                static fn ($name): ObjectType => new ObjectType($name->getValue()),
                $classNames
            );

            $narrowedType = count($objectTypes) === 1
                ? $objectTypes[0]
                : new UnionType($objectTypes);

            return new GenericObjectType(Expectation::class, [$narrowedType]);
        }

        return new GenericObjectType(Expectation::class, [new ObjectWithoutClassType()]);
    }
}
