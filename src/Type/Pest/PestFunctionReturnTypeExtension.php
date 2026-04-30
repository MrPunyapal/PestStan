<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use Pest\Configuration;
use Pest\Expectation;
use Pest\PendingCalls\AfterEachCall;
use Pest\PendingCalls\BeforeEachCall;
use Pest\PendingCalls\DescribeCall;
use Pest\PendingCalls\TestCall;
use Pest\PendingCalls\UsesCall;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;

/**
 * Provides proper return types for Pest's global functions.
 */
final class PestFunctionReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{
    /** @var array<string, class-string> Maps function names to return class names */
    private const FUNCTION_RETURN_TYPES = [
        'pest' => Configuration::class,
        'uses' => UsesCall::class,
        'it' => TestCall::class,
        'test' => TestCall::class,
        'todo' => TestCall::class,
        'describe' => DescribeCall::class,
        'beforeEach' => BeforeEachCall::class,
        'afterEach' => AfterEachCall::class,
    ];

    /** @var list<string> */
    private const NULL_RETURN_TYPES = [
        'beforeAll',
        'afterAll',
        'dataset',
        'covers',
        'mutates',
    ];

    /** @var list<string> */
    private const STRING_RETURN_TYPES = [
        'fixture',
    ];

    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        $name = $functionReflection->getName();

        return $name === 'expect'
            || isset(self::FUNCTION_RETURN_TYPES[$name])
            || in_array($name, self::STRING_RETURN_TYPES, true)
            || in_array($name, self::NULL_RETURN_TYPES, true);
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

        if (in_array($name, self::NULL_RETURN_TYPES, true)) {
            return new NullType;
        }

        if (in_array($name, self::STRING_RETURN_TYPES, true)) {
            return new StringType;
        }

        return new ObjectType(self::FUNCTION_RETURN_TYPES[$name]);
    }

    private function resolveExpect(FuncCall $functionCall, Scope $scope): Type
    {
        $args = $functionCall->getArgs();

        if ($args === []) {
            return new GenericObjectType(Expectation::class, [new NullType]);
        }

        $valueType = $scope->getType($args[0]->value);

        return new GenericObjectType(Expectation::class, [$valueType]);
    }
}
