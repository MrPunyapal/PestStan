<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicFunctionThisTypeExtension;
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
final class TestClosureThisTypeExtension implements DynamicFunctionThisTypeExtension
{
    private const PEST_TEST_FUNCTIONS = [
        'Pest\it',
        'Pest\test',
    ];

    private const PEST_HOOK_FUNCTIONS = [
        'Pest\beforeEach',
        'Pest\afterEach',
        'Pest\beforeAll',
        'Pest\afterAll',
    ];

    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        $functionName = $functionReflection->getName();
        
        return in_array($functionName, self::PEST_TEST_FUNCTIONS, true)
            || in_array($functionName, self::PEST_HOOK_FUNCTIONS, true);
    }

    public function getThisTypeFromFunctionCall(
        FunctionReflection $functionReflection,
        FuncCall $functionCall,
        Scope $scope
    ): ?Type {
        // Get the closure argument - it's typically the last argument
        $args = $functionCall->getArgs();
        if (count($args) === 0) {
            return null;
        }

        $closureArg = end($args);
        $closureType = $scope->getType($closureArg->value);

        // Only process if it's actually a closure
        if (!$closureType instanceof ClosureType) {
            return null;
        }

        // Try to determine the TestCase class from the context
        // This would ideally parse uses() or pest()->extend() calls
        // For now, we default to PHPUnit\Framework\TestCase
        return new ObjectType(TestCase::class);
    }
}
