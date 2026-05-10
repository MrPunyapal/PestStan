<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use Pest\Expectation;
use Pest\Mixins\Expectation as MixinsExpectation;
use PestStan\Analysis\Expectation\ExpectationMatcherRegistry;
use PestStan\Analysis\Expectation\ExpectationTypeNarrower;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;

/**
 * Fixes return types for assertion methods on Pest\Mixins\Expectation.
 */
final class ExpectationMethodReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    private readonly ExpectationMatcherRegistry $matcherRegistry;

    private readonly ExpectationTypeNarrower $typeNarrower;

    public function __construct(
        ?ExpectationMatcherRegistry $matcherRegistry = null,
        ?ExpectationTypeNarrower $typeNarrower = null,
    ) {
        $this->matcherRegistry = $matcherRegistry ?? new ExpectationMatcherRegistry;
        $this->typeNarrower = $typeNarrower ?? new ExpectationTypeNarrower;
    }

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

        $valueType = $scope->getType($methodCall->var)
            ->getTemplateType(Expectation::class, 'TValue');

        $assertedType = $this->matcherRegistry->assertedTypeFor($methodName, $methodCall, $scope);
        if ($assertedType instanceof Type) {
            return new GenericObjectType(
                Expectation::class,
                [$this->typeNarrower->narrow($valueType, $assertedType)],
            );
        }

        return new GenericObjectType(Expectation::class, [$valueType]);
    }
}
