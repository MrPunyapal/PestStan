<?php

declare(strict_types=1);

namespace PestStan\Rules;

use Pest\Configuration;
use Pest\PendingCalls\TestCall;
use Pest\PendingCalls\UsesCall;
use PestStan\Diagnostics\PestDiagnosticIdentifiers;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

/**
 * Detects empty string arguments passed to group().
 *
 * @implements Rule<MethodCall>
 */
final class InvalidGroupNameRule implements Rule
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
        if (! $node->name instanceof Identifier || $node->name->name !== 'group') {
            return [];
        }

        $callerType = $scope->getType($node->var);
        if (! $this->isSupportedGroupCaller($callerType)) {
            return [];
        }

        $args = $node->getArgs();
        if ($args === []) {
            return [
                RuleErrorBuilder::message('group() requires at least one non-empty string argument.')
                    ->identifier(PestDiagnosticIdentifiers::GROUP_INVALID_NAME)
                    ->build(),
            ];
        }

        foreach ($args as $arg) {
            if ($arg->value instanceof String_ && trim($arg->value->value) === '') {
                return [
                    RuleErrorBuilder::message('group() requires a non-empty string argument.')
                        ->identifier(PestDiagnosticIdentifiers::GROUP_INVALID_NAME)
                        ->build(),
                ];
            }
        }

        return [];
    }

    private function isSupportedGroupCaller(Type $callerType): bool
    {
        foreach ([TestCall::class, UsesCall::class, Configuration::class] as $supportedCaller) {
            if ((new ObjectType($supportedCaller))->isSuperTypeOf($callerType)->yes()) {
                return true;
            }
        }

        return false;
    }
}
