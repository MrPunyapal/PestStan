<?php

declare(strict_types=1);

namespace PestStan\Rules;

use PestStan\Diagnostics\PestDiagnosticIdentifiers;
use PestStan\PestFunctionDetector;
use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Detects beforeAll() and afterAll() calls inside describe() blocks, including nested describes.
 *
 * @implements Rule<FuncCall>
 */
final class DisallowedCallInDescribeRule implements Rule
{
    /** @var array<string, string> */
    private const FORBIDDEN_FUNCTIONS = [
        'beforeAll' => PestDiagnosticIdentifiers::DESCRIBE_BEFORE_ALL_DISALLOWED,
        'afterAll' => PestDiagnosticIdentifiers::DESCRIBE_AFTER_ALL_DISALLOWED,
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
        if (! PestFunctionDetector::isDescribeFunction($node)) {
            return [];
        }

        $closure = PestFunctionDetector::extractClosure($node);
        if (! $closure instanceof Closure) {
            return [];
        }

        return $this->collectForbiddenCalls($closure);
    }

    /**
     * Recursively collects forbidden calls in a describe closure and nested describe closures.
     *
     * @return list<IdentifierRuleError>
     */
    private function collectForbiddenCalls(Closure $closure): array
    {
        $errors = [];

        foreach ($closure->stmts as $stmt) {
            if (! $stmt instanceof Expression) {
                continue;
            }

            if (! $stmt->expr instanceof FuncCall) {
                continue;
            }

            $call = $stmt->expr;
            if (! $call->name instanceof Name) {
                continue;
            }

            $name = $call->name->toString();

            if (isset(self::FORBIDDEN_FUNCTIONS[$name])) {
                $errors[] = RuleErrorBuilder::message(
                    sprintf('%s() cannot be used inside describe() blocks. Use %s instead.', $name, $name === 'beforeAll' ? 'beforeEach()' : 'afterEach()')
                )
                    ->identifier(self::FORBIDDEN_FUNCTIONS[$name])
                    ->line($call->getStartLine())
                    ->build();
            }

            if ($name === 'describe') {
                $nestedClosure = PestFunctionDetector::extractClosure($call);
                if ($nestedClosure instanceof Closure) {
                    $errors = array_merge($errors, $this->collectForbiddenCalls($nestedClosure));
                }
            }
        }

        return $errors;
    }
}
