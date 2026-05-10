<?php

declare(strict_types=1);

namespace PestStan\Rules;

use PestStan\Diagnostics\PestDiagnosticIdentifiers;
use PestStan\Diagnostics\PestDiagnostics;
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

/**
 * Detects $this usage inside static Pest hook closures.
 *
 * @implements Rule<FuncCall>
 */
final class BeforeAllThisUsageRule implements Rule
{
    /** @var array<string, array{identifier: string, replacement: string}> */
    private const STATIC_HOOKS = [
        'beforeAll' => [
            'identifier' => PestDiagnosticIdentifiers::LIFECYCLE_BEFORE_ALL_THIS_USAGE,
            'replacement' => 'beforeEach',
        ],
        'afterAll' => [
            'identifier' => PestDiagnosticIdentifiers::LIFECYCLE_AFTER_ALL_THIS_USAGE,
            'replacement' => 'afterEach',
        ],
    ];

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

        $functionName = $node->name->toString();
        if (! isset(self::STATIC_HOOKS[$functionName])) {
            return [];
        }

        $closure = PestFunctionDetector::extractClosure($node);
        if (! $closure instanceof Closure) {
            return [];
        }

        return $this->findThisUsages($closure, $functionName);
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function findThisUsages(Closure $closure, string $functionName): array
    {
        $errors = [];

        foreach ($closure->stmts as $stmt) {
            if (! $stmt instanceof Expression) {
                continue;
            }

            $this->walkExprForThis($stmt->expr, $errors, $functionName);
        }

        return $errors;
    }

    /**
     * @param  list<IdentifierRuleError>  $errors
     */
    private function walkExprForThis(Expr $expr, array &$errors, string $functionName): void
    {
        if ($expr instanceof PropertyFetch && $expr->var instanceof Variable && $expr->var->name === 'this') {
            $errors[] = $this->buildStaticHookError($functionName, $expr->getStartLine());

            return;
        }

        if ($expr instanceof MethodCall && $expr->var instanceof Variable && $expr->var->name === 'this') {
            $errors[] = $this->buildStaticHookError($functionName, $expr->getStartLine());

            return;
        }

        if ($expr instanceof Assign) {
            $this->walkExprForThis($expr->var, $errors, $functionName);
            $this->walkExprForThis($expr->expr, $errors, $functionName);
        }
    }

    private function buildStaticHookError(string $functionName, int $line): IdentifierRuleError
    {
        $hookConfig = self::STATIC_HOOKS[$functionName];

        return PestDiagnostics::toRuleError(
            PestDiagnostics::invalidLifecycleThisUsage(
                $functionName,
                $hookConfig['replacement'],
                $hookConfig['identifier'],
                $line,
            )
        );
    }
}
