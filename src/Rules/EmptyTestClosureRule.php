<?php

declare(strict_types=1);

namespace PestStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Nop;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Detects test closures with empty bodies.
 *
 * @implements Rule<FuncCall>
 */
final class EmptyTestClosureRule implements Rule
{
    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->name instanceof Name) {
            return [];
        }

        $funcName = $node->name->toString();
        if ($funcName !== 'it' && $funcName !== 'test') {
            return [];
        }

        $args = $node->getArgs();
        $closureArgIndex = 1;
        if (! isset($args[$closureArgIndex])) {
            return [];
        }

        $closure = $args[$closureArgIndex]->value;
        if (! $closure instanceof Closure) {
            return [];
        }

        $realStmts = array_filter(
            $closure->stmts,
            static fn (Node $stmt): bool => ! $stmt instanceof Nop
        );

        if ($realStmts !== []) {
            return [];
        }

        $description = '';
        if (isset($args[0]) && $args[0]->value instanceof String_) {
            $description = $args[0]->value->value;
        }

        return [
            RuleErrorBuilder::message(
                sprintf("Test '%s' has an empty closure body. Did you forget to add assertions?", $description)
            )
                ->identifier('pest.emptyTestClosure')
                ->build(),
        ];
    }
}
