<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use Pest\Expectation;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\MethodTypeSpecifyingExtension;

/**
 * TODO: This extension is currently disabled.
 *
 * Goal: After calling expect($value)->toBeString(), PHPStan should know that $value is a string.
 *
 * Current Limitation: PHPStan's MethodTypeSpecifyingExtension cannot narrow the type of the
 * original variable passed to expect() in fluent chains. The type-specifying mechanism is
 * designed for conditional type narrowing (e.g., inside if statements), not for assertion-style
 * method chains.
 *
 * This extension is kept for future reference when PHPStan may support this capability.
 *
 * @see https://phpstan.org/developing-extensions/type-specifying-extensions
 */
final class ExpectationTypeSpecifyingExtension implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
    public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
    {
        // Required by TypeSpecifierAwareExtension interface
        // Not used since this extension is currently disabled
    }

    public function getClass(): string
    {
        return Expectation::class;
    }

    public function isMethodSupported(
        MethodReflection $methodReflection,
        MethodCall $node,
        TypeSpecifierContext $context
    ): bool {
        // return in_array($methodReflection->getName(), [
        //     'toBeString',
        //     'toBeInt',
        //     'toBeFloat',
        //     'toBeBool',
        //     'toBeArray',
        //     'toBeObject',
        //     'toBeResource',
        //     'toBeCallable',
        //     'toBeIterable',
        //     'toBeNumeric',
        //     'toBeScalar',
        //     'toBeTrue',
        //     'toBeFalse',
        //     'toBeNull',
        //     'toBeInstanceOf',
        // ], true);

        return false; // Disabled - see TODO above
    }

    public function specifyTypes(
        MethodReflection $methodReflection,
        MethodCall $node,
        Scope $scope,
        TypeSpecifierContext $context
    ): SpecifiedTypes {
        return new SpecifiedTypes();
    }
}
