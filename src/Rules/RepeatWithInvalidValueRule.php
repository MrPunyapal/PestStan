<?php

declare(strict_types=1);

namespace PestStan\Rules;

use Pest\PendingCalls\TestCall;
use PestStan\Diagnostics\PestDiagnosticIdentifiers;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * Detects repeat() calls with a value less than 1.
 *
 * @implements Rule<MethodCall>
 */
final class RepeatWithInvalidValueRule implements Rule
{
    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->name instanceof Identifier || $node->name->name !== 'repeat') {
            return [];
        }

        $callerType = $scope->getType($node->var);
        if (! (new ObjectType(TestCall::class))->isSuperTypeOf($callerType)->yes()) {
            return [];
        }

        $args = $node->getArgs();
        if ($args === []) {
            return [];
        }

        $argType = $scope->getType($args[0]->value);

        foreach ($argType->getConstantScalarValues() as $value) {
            if (is_int($value) && $value < 1) {
                return [
                    RuleErrorBuilder::message(
                        sprintf('repeat() requires a value greater than 0, got %d.', $value)
                    )
                        ->identifier(PestDiagnosticIdentifiers::REPEAT_INVALID_VALUE)
                        ->build(),
                ];
            }
        }

        return [];
    }
}
