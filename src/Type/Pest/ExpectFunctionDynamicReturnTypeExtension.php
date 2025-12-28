<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

/**
 * Provides proper return type for the expect() function.
 * 
 * expect($value) returns Expectation<typeof $value>
 */
final class ExpectFunctionDynamicReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{
    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        return $functionReflection->getName() === 'Pest\expect';
    }

    public function getTypeFromFunctionCall(
        FunctionReflection $functionReflection,
        FuncCall $functionCall,
        Scope $scope
    ): ?Type {
        $args = $functionCall->getArgs();
        
        if (count($args) === 0) {
            return null;
        }

        $valueType = $scope->getType($args[0]->value);

        // Return Expectation<TValue> where TValue is the type of the passed value
        return new GenericObjectType(
            'Pest\Expectation',
            [$valueType]
        );
    }
}
