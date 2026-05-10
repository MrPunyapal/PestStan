<?php

declare(strict_types=1);

namespace PestStan\Rules;

use PestStan\Diagnostics\PestDiagnosticIdentifiers;
use PestStan\PestFunctionDetector;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Detects describe() blocks that contain no it()/test() calls.
 *
 * @implements Rule<FuncCall>
 */
final class DescribeWithoutTestsRule implements Rule
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
        if (! PestFunctionDetector::isDescribeFunction($node)) {
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

        if ($realStmts === []) {
            $description = $this->extractDescribeDescription($node);

            return [
                RuleErrorBuilder::message(
                    sprintf("describe() block '%s' contains no tests.", $description)
                )
                    ->identifier(PestDiagnosticIdentifiers::DESCRIBE_WITHOUT_TESTS)
                    ->build(),
            ];
        }

        if ($this->containsTestCall($closure)) {
            return [];
        }

        $description = $this->extractDescribeDescription($node);

        return [
            RuleErrorBuilder::message(
                sprintf("describe() block '%s' contains no tests.", $description)
            )
                ->identifier(PestDiagnosticIdentifiers::DESCRIBE_WITHOUT_TESTS)
                ->build(),
        ];
    }

    private function containsTestCall(Closure $closure): bool
    {
        foreach ($closure->stmts as $stmt) {
            if (! $stmt instanceof Expression) {
                continue;
            }

            $call = $this->extractRootCall($stmt->expr);
            if (! $call instanceof FuncCall) {
                continue;
            }

            if (! $call->name instanceof Name) {
                continue;
            }

            if (PestFunctionDetector::isTestFunction($call) || PestFunctionDetector::isDescribeFunction($call)) {
                return true;
            }
        }

        return false;
    }

    private function extractRootCall(Expr $expr): ?FuncCall
    {
        while ($expr instanceof MethodCall) {
            $expr = $expr->var;
        }

        return $expr instanceof FuncCall ? $expr : null;
    }

    private function extractDescribeDescription(FuncCall $node): string
    {
        $args = $node->getArgs();
        if ($args === []) {
            return '';
        }

        $firstArg = $args[0]->value;
        if ($firstArg instanceof String_) {
            return $firstArg->value;
        }

        return '';
    }
}
