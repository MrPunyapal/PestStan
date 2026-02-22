<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use Pest\Expectation;
use Pest\PendingCalls\DescribeCall;
use Pest\PendingCalls\TestCall;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

/**
 * Provides proper return types for Pest's global functions.
 */
final class PestFunctionReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{
    /** @var array<string, class-string> Maps function names to return class names */
    private const FUNCTION_RETURN_TYPES = [
        'it' => TestCall::class,
        'test' => TestCall::class,
        'todo' => TestCall::class,
        'describe' => DescribeCall::class,
    ];

    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        $name = $functionReflection->getName();

        return $name === 'expect' || isset(self::FUNCTION_RETURN_TYPES[$name]);
    }

    public function getTypeFromFunctionCall(
        FunctionReflection $functionReflection,
        FuncCall $functionCall,
        Scope $scope
    ): Type {
        $name = $functionReflection->getName();

        if ($name === 'expect') {
            return $this->resolveExpect($functionCall, $scope);
        }

        return new ObjectType(self::FUNCTION_RETURN_TYPES[$name]);
    }

    private function resolveExpect(FuncCall $functionCall, Scope $scope): Type
    {
        $args = $functionCall->getArgs();

        if ($args === []) {
            return new GenericObjectType(Expectation::class, [new NullType()]);
        }

        $valueType = $scope->getType($args[0]->value);

        return new GenericObjectType(Expectation::class, [$valueType]);
    }
}
