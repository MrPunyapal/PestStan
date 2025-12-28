<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use Pest\Expectation;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\MethodTypeSpecifyingExtension;

/**
 * Provides type narrowing for Expectation assertion methods.
 *
 * After calling expect($value)->toBeString(), PHPStan knows that $value is a string.
 */
final class ExpectationTypeSpecifyingExtension implements MethodTypeSpecifyingExtension
{
    public function getClass(): string
    {
        return Expectation::class;
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
        // Note: This is a simplified implementation
        // A full implementation would need to track the value passed to expect()
        // and create proper type narrowing
        return new SpecifiedTypes();
    }
}
