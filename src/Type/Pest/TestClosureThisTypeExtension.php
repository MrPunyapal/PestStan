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
 *
 * When a closure is passed to it(), test(), beforeEach(), etc., the closure
 * is bound to a TestCase instance at runtime. This extension helps PHPStan
 * understand that $this inside these closures refers to the TestCase.
 */
final class TestClosureThisTypeExtension implements FunctionParameterClosureThisExtension
{
    private const PEST_TEST_FUNCTIONS = [
        'Pest\it',
        'Pest\test',
        'Pest\describe',
    ];

    private const PEST_HOOK_FUNCTIONS = [
        'Pest\beforeEach',
        'Pest\afterEach',
        'Pest\beforeAll',
        'Pest\afterAll',
    ];

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
        // Default to PHPUnit\Framework\TestCase
        // Custom TestCase detection via uses() or pest()->extend() would require
        // parsing the file's AST, which is complex to do correctly.
        return new ObjectType(TestCase::class);
    }
}
