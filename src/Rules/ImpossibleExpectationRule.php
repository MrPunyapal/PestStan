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
 * Detects type assertion expectations that will always fail.
 *
 * @implements Rule<MethodCall>
 */
final class ImpossibleExpectationRule implements Rule
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

        if (! $this->isImpossible($methodName, $valueType, $node, $scope)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Calling %s() on Expectation<%s> will always fail.',
                    $methodName,
                    $valueType->describe(VerbosityLevel::typeOnly())
                )
            )
                ->identifier('pest.impossibleExpectation')
                ->build(),
        ];
    }

    private function isImpossible(string $method, Type $valueType, MethodCall $node, Scope $scope): bool
    {
        return match ($method) {
            'toBeString' => $valueType->isString()->no(),
            'toBeInt' => $valueType->isInteger()->no(),
            'toBeFloat' => $valueType->isFloat()->no(),
            'toBeBool' => $valueType->isTrue()->no() && $valueType->isFalse()->no(),
            'toBeTrue' => $valueType->isTrue()->no(),
            'toBeFalse' => $valueType->isFalse()->no(),
            'toBeNull' => $valueType->isNull()->no(),
            'toBeArray' => $valueType->isArray()->no(),
            'toBeList' => $valueType->isList()->no(),
            'toBeObject' => $valueType->isObject()->no(),
            'toBeCallable' => $valueType->isCallable()->no(),
            'toBeIterable' => $valueType->isIterable()->no(),
            'toBeNumeric' => $this->isNeverNumeric($valueType),
            'toBeScalar' => $this->isNeverScalar($valueType),
            'toBeInstanceOf' => $this->isInstanceOfImpossible($valueType, $node, $scope),
            default => false,
        };
    }

    private function isNeverNumeric(Type $type): bool
    {
        return $type->isInteger()->no()
            && $type->isFloat()->no()
            && $type->isString()->no();
    }

    private function isNeverScalar(Type $type): bool
    {
        return $type->isString()->no()
            && $type->isInteger()->no()
            && $type->isFloat()->no()
            && $type->isTrue()->no()
            && $type->isFalse()->no();
    }

    private function isInstanceOfImpossible(Type $valueType, MethodCall $node, Scope $scope): bool
    {
        $args = $node->getArgs();
        if ($args === []) {
            return false;
        }

        $classType = $scope->getType($args[0]->value);
        $classNames = $classType->getConstantStrings();

        if ($classNames === []) {
            return $valueType->isObject()->no();
        }

        foreach ($classNames as $className) {
            $assertedType = new ObjectType($className->getValue());
            if (! $valueType->isSuperTypeOf($assertedType)->no()) {
                return false;
            }
        }

        return true;
    }
}
