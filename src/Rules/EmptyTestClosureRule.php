<?php

declare(strict_types=1);

namespace PestStan\Rules;

use PestStan\Diagnostics\PestDiagnosticIdentifiers;
use PestStan\PestFunctionDetector;
use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
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
        if (! PestFunctionDetector::isTestFunction($node)) {
            return [];
        }

        $closure = PestFunctionDetector::extractClosure($node);
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

        $description = PestFunctionDetector::extractDescription($node) ?? '';

        return [
            RuleErrorBuilder::message(
                sprintf("Test '%s' has an empty closure body. Add assertions or chain ->todo() to mark as pending.", $description)
            )
                ->identifier(PestDiagnosticIdentifiers::TEST_EMPTY_CLOSURE)
                ->build(),
        ];
    }
}
