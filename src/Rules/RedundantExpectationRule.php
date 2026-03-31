<?php

declare(strict_types=1);

namespace PestStan\Rules;

use Pest\Expectation;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

/**
 * Detects type assertion expectations that will always pass (redundant assertions).
 *
 * @implements Rule<MethodCall>
 */
final class RedundantExpectationRule implements Rule
{
    /** @var list<string> */
    private const TYPE_ASSERTION_METHODS = [
        'toBeString',
        'toBeInt',
        'toBeFloat',
        'toBeBool',
        'toBeTrue',
        'toBeFalse',
        'toBeNull',
        'toBeArray',
        'toBeList',
        'toBeObject',
        'toBeCallable',
        'toBeIterable',
        'toBeNumeric',
        'toBeScalar',
        'toBeInstanceOf',
    ];

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->name instanceof Identifier) {
            return [];
        }

        $methodName = $node->name->name;
        if (! in_array($methodName, self::TYPE_ASSERTION_METHODS, true)) {
            return [];
        }

        $callerType = $scope->getType($node->var);
        if (! (new ObjectType(Expectation::class))->isSuperTypeOf($callerType)->yes()) {
            return [];
        }

        $valueType = $callerType->getTemplateType(Expectation::class, 'TValue');
        if ($valueType instanceof MixedType) {
            return [];
        }

        if (! $this->isTrivial($methodName, $valueType, $node, $scope)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Calling %s() on Expectation<%s> will always pass — the assertion is redundant.',
                    $methodName,
                    $valueType->describe(VerbosityLevel::typeOnly())
                )
            )
                ->identifier('pest.redundantExpectation')
                ->tip('Consider removing this assertion — the value is already guaranteed to be this type.')
                ->build(),
        ];
    }

    private function isTrivial(string $method, Type $valueType, MethodCall $node, Scope $scope): bool
    {
        return match ($method) {
            'toBeString' => $valueType->isString()->yes(),
            'toBeInt' => $valueType->isInteger()->yes(),
            'toBeFloat' => $valueType->isFloat()->yes(),
            'toBeBool' => $valueType->isTrue()->yes() || $valueType->isFalse()->yes(),
            'toBeTrue' => $valueType->isTrue()->yes(),
            'toBeFalse' => $valueType->isFalse()->yes(),
            'toBeNull' => $valueType->isNull()->yes(),
            'toBeArray' => $valueType->isArray()->yes(),
            'toBeList' => $valueType->isList()->yes(),
            'toBeObject' => $valueType->isObject()->yes(),
            'toBeCallable' => $valueType->isCallable()->yes(),
            'toBeIterable' => $valueType->isIterable()->yes(),
            'toBeNumeric' => $valueType->isInteger()->yes() || $valueType->isFloat()->yes(),
            'toBeScalar' => $this->isAlwaysScalar($valueType),
            'toBeInstanceOf' => $this->isInstanceOfTrivial($valueType, $node, $scope),
            default => false,
        };
    }

    private function isAlwaysScalar(Type $type): bool
    {
        if ($type->isString()->yes()) {
            return true;
        }

        if ($type->isInteger()->yes()) {
            return true;
        }

        if ($type->isFloat()->yes()) {
            return true;
        }

        if ($type->isTrue()->yes()) {
            return true;
        }

        return $type->isFalse()->yes();
    }

    private function isInstanceOfTrivial(Type $valueType, MethodCall $node, Scope $scope): bool
    {
        $args = $node->getArgs();
        if ($args === []) {
            return false;
        }

        $classType = $scope->getType($args[0]->value);
        $classNames = $classType->getConstantStrings();

        if ($classNames === []) {
            return false;
        }

        foreach ($classNames as $className) {
            $assertedType = new ObjectType($className->getValue());
            if (! $assertedType->isSuperTypeOf($valueType)->yes()) {
                return false;
            }
        }

        return true;
    }
}
