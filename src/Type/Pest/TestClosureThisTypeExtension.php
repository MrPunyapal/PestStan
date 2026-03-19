<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\FunctionParameterClosureThisExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPUnit\Framework\TestCase;

/**
 * Determines the $this type for closures passed to Pest test functions.
 */
final class TestClosureThisTypeExtension implements FunctionParameterClosureThisExtension
{
    private const PEST_TEST_FUNCTIONS = [
        'it',
        'test',
        'describe',
    ];

    private const PEST_HOOK_FUNCTIONS = [
        'beforeEach',
        'afterEach',
        'beforeAll',
        'afterAll',
    ];

    /** @param class-string $testCaseClass */
    public function __construct(
        private readonly PestConfigReader $pestConfigReader,
        private readonly string $testCaseClass = TestCase::class,
    ) {}

    public function isFunctionSupported(FunctionReflection $functionReflection, ParameterReflection $parameter): bool
    {
        $functionName = $functionReflection->getName();

        return in_array($functionName, self::PEST_TEST_FUNCTIONS, true)
            || in_array($functionName, self::PEST_HOOK_FUNCTIONS, true);
    }

    public function getClosureThisTypeFromFunctionCall(
        FunctionReflection $functionReflection,
        FuncCall $functionCall,
        ParameterReflection $parameter,
        Scope $scope
    ): Type {
        $resolvedClass = $this->pestConfigReader->resolveTestCaseClass($scope->getFile());
        $class = $resolvedClass ?? $this->testCaseClass;

        return new ObjectType($class);
    }
}
