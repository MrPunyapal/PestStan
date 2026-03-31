<?php

declare(strict_types=1);

namespace PestStan\Rules;

use PestStan\PestFunctionDetector;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Detects test closures that have no assertions (no expect() or $this->assert*() calls).
 *
 * @implements Rule<Expression>
 */
final class MissingAssertionRule implements Rule
{
    private const THROWS_METHODS = ['throws', 'throwsIf', 'throwsUnless', 'throwsNoExceptions'];

    public function getNodeType(): string
    {
        return Expression::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $expr = $node->expr;

        /** @var list<string> $chainMethods */
        $chainMethods = [];
        while ($expr instanceof MethodCall) {
            if ($expr->name instanceof Identifier) {
                $chainMethods[] = $expr->name->name;
            }

            $expr = $expr->var;
        }

        if (! $expr instanceof FuncCall || ! PestFunctionDetector::isTestFunction($expr)) {
            return [];
        }

        if (array_intersect($chainMethods, self::THROWS_METHODS) !== []) {
            return [];
        }

        $closure = PestFunctionDetector::extractClosure($expr);
        if (! $closure instanceof Closure) {
            return [];
        }

        $realStmts = array_filter(
            $closure->stmts,
            static fn (Node $stmt): bool => ! $stmt instanceof Nop
        );

        if ($realStmts === []) {
            return [];
        }

        if ($this->containsAssertion($closure)) {
            return [];
        }

        $description = PestFunctionDetector::extractDescription($expr) ?? '';

        return [
            RuleErrorBuilder::message(
                sprintf("Test '%s' has no assertions. Did you forget expect()?", $description)
            )
                ->identifier('pest.missingAssertion')
                ->line($expr->getStartLine())
                ->build(),
        ];
    }

    private function containsAssertion(Closure $closure): bool
    {
        foreach ($closure->stmts as $stmt) {
            if ($this->stmtContainsAssertion($stmt)) {
                return true;
            }
        }

        return false;
    }

    private function stmtContainsAssertion(Node $node): bool
    {
        if ($node instanceof Expression) {
            return $this->exprContainsAssertion($node->expr);
        }

        return false;
    }

    private function exprContainsAssertion(Expr $expr): bool
    {
        if ($expr instanceof FuncCall && $expr->name instanceof Name) {
            $name = $expr->name->toString();
            if ($name === 'expect' || str_starts_with($name, 'assert')) {
                return true;
            }
        }

        if ($expr instanceof MethodCall) {
            if ($this->isAssertMethodCall($expr)) {
                return true;
            }

            return $this->exprContainsAssertion($expr->var);
        }

        return false;
    }

    private function isAssertMethodCall(MethodCall $call): bool
    {
        if (! $call->name instanceof Identifier) {
            return false;
        }

        $methodName = $call->name->name;

        return str_starts_with($methodName, 'assert') || str_starts_with($methodName, 'expect');
    }
}
