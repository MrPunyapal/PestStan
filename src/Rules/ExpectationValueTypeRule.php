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
use PHPStan\Type\VerbosityLevel;

/**
 * Detects expectation methods called on incompatible value types.
 *
 * @implements Rule<MethodCall>
 */
final class ExpectationValueTypeRule implements Rule
{
    /** @var list<string> */
    private const REQUIRES_ITERABLE = ['each', 'sequence'];

    /** @var list<string> */
    private const REQUIRES_STRING = [
        'json',
        'toStartWith',
        'toEndWith',
        'toBeJson',
        'toBeDirectory',
        'toBeFile',
        'toBeReadableFile',
        'toBeWritableFile',
        'toBeReadableDirectory',
        'toBeWritableDirectory',
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
        $requiresIterable = in_array($methodName, self::REQUIRES_ITERABLE, true);
        $requiresString = in_array($methodName, self::REQUIRES_STRING, true);

        if (! $requiresIterable && ! $requiresString) {
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

        $typeDesc = $valueType->describe(VerbosityLevel::typeOnly());

        if ($requiresIterable && $valueType->isIterable()->no()) {
            return [
                RuleErrorBuilder::message(
                    sprintf('Calling %s() on Expectation<%s> — value is not iterable.', $methodName, $typeDesc)
                )
                    ->identifier('pest.expectationRequiresIterable')
                    ->tip('Pass an iterable value to expect() before calling ' . $methodName . '().')
                    ->build(),
            ];
        }

        if ($requiresString && $valueType->isString()->no()) {
            return [
                RuleErrorBuilder::message(
                    sprintf('Calling %s() on Expectation<%s> — value must be a string.', $methodName, $typeDesc)
                )
                    ->identifier('pest.expectationRequiresString')
                    ->tip('Pass a string value to expect() before calling ' . $methodName . '().')
                    ->build(),
            ];
        }

        return [];
    }
}
