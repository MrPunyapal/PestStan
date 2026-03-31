<?php

declare(strict_types=1);

namespace PestStan\Rules;

use PestStan\PestFunctionDetector;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Detects $this usage inside beforeAll() closures, which run in static context.
 *
 * @implements Rule<FuncCall>
 */
final class BeforeAllThisUsageRule implements Rule
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
        if (! $node->name instanceof Name || $node->name->toString() !== 'beforeAll') {
            return [];
        }

        $closure = PestFunctionDetector::extractClosure($node);
        if (! $closure instanceof Closure) {
            return [];
        }

        return $this->findThisUsages($closure);
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function findThisUsages(Closure $closure): array
    {
        $errors = [];

        foreach ($closure->stmts as $stmt) {
            if (! $stmt instanceof Expression) {
                continue;
            }

            $this->walkExprForThis($stmt->expr, $errors);
        }

        return $errors;
    }

    /**
     * @param  list<IdentifierRuleError>  $errors
     */
    private function walkExprForThis(Expr $expr, array &$errors): void
    {
        if ($expr instanceof PropertyFetch && $expr->var instanceof Variable && $expr->var->name === 'this') {
            $errors[] = RuleErrorBuilder::message(
                'beforeAll() runs in static context — $this is not available. Use beforeEach() instead.'
            )
                ->identifier('pest.beforeAllThisUsage')
                ->line($expr->getStartLine())
                ->tip('This can be auto-fixed with rector-pest.')
                ->build();

            return;
        }

        if ($expr instanceof MethodCall && $expr->var instanceof Variable && $expr->var->name === 'this') {
            $errors[] = RuleErrorBuilder::message(
                'beforeAll() runs in static context — $this is not available. Use beforeEach() instead.'
            )
                ->identifier('pest.beforeAllThisUsage')
                ->line($expr->getStartLine())
                ->tip('This can be auto-fixed with rector-pest.')
                ->build();

            return;
        }

        if ($expr instanceof Assign) {
            $this->walkExprForThis($expr->var, $errors);
            $this->walkExprForThis($expr->expr, $errors);
        }
    }
}
