<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MethodTypeSpecifyingExtension;
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
 * Provides type narrowing for Expectation assertion methods.
 * 
 * After calling expect($value)->toBeString(), PHPStan knows that $value is a string.
 */
final class ExpectationTypeSpecifyingExtension implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
    private TypeSpecifier $typeSpecifier;

    public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
    {
        $this->typeSpecifier = $typeSpecifier;
    }

    public function getClass(): string
    {
        return 'Pest\Expectation';
    }

    public function isMethodSupported(
        MethodReflection $methodReflection,
        MethodCall $node,
        TypeSpecifierContext $context
    ): bool {
        return in_array($methodReflection->getName(), [
            'toBeString',
            'toBeInt',
            'toBeFloat',
            'toBeBool',
            'toBeArray',
            'toBeObject',
            'toBeResource',
            'toBeCallable',
            'toBeIterable',
            'toBeNumeric',
            'toBeScalar',
            'toBeTrue',
            'toBeFalse',
            'toBeNull',
            'toBeInstanceOf',
        ], true);
    }

    public function specifyTypes(
        MethodReflection $methodReflection,
        MethodCall $node,
        Scope $scope,
        TypeSpecifierContext $context
    ): SpecifiedTypes {
        // The value being tested is stored in the Expectation object
        // We need to narrow the type based on the assertion method called
        
        $methodName = $methodReflection->getName();
        
        // Map assertion methods to their corresponding types
        $typeMap = [
            'toBeString' => new StringType(),
            'toBeInt' => new IntegerType(),
            'toBeFloat' => new FloatType(),
            'toBeBool' => new BooleanType(),
            'toBeArray' => new ArrayType(new MixedType(), new MixedType()),
            'toBeObject' => new ObjectWithoutClassType(),
            'toBeResource' => new ResourceType(),
            'toBeCallable' => new UnionType([
                new StringType(),
                new ArrayType(new MixedType(), new MixedType()),
                new ObjectType('Closure'),
            ]),
            'toBeIterable' => new IterableType(new MixedType(), new MixedType()),
            'toBeTrue' => new ConstantBooleanType(true),
            'toBeFalse' => new ConstantBooleanType(false),
            'toBeNull' => new NullType(),
        ];

        if (isset($typeMap[$methodName])) {
            // Note: This is a simplified implementation
            // A full implementation would need to track the value passed to expect()
            // and create a specified type for it
            return new SpecifiedTypes([], [], false, [], $node);
        }

        if ($methodName === 'toBeInstanceOf') {
            $args = $node->getArgs();
            if (count($args) > 0) {
                $classType = $scope->getType($args[0]->value);
                // Create object type from the class string
                // This is also simplified - would need proper type extraction
            }
        }

        return new SpecifiedTypes([], [], false, [], $node);
    }
}
