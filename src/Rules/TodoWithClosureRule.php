<?php

declare(strict_types=1);

namespace PestStan\Rules;

use PestStan\PestFunctionDetector;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Detects tests marked as todo() that still have a closure body (dead code).
 *
 * @implements Rule<MethodCall>
 */
final class TodoWithClosureRule implements Rule
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
        if (! $node->name instanceof Identifier || $node->name->name !== 'todo') {
            return [];
        }

        $testCall = $this->findTestCall($node);
        if (! $testCall instanceof FuncCall) {
            return [];
        }

        $closure = PestFunctionDetector::extractClosure($testCall);
        if ($closure === null) {
            return [];
        }

        $description = PestFunctionDetector::extractDescription($testCall) ?? '';

        return [
            RuleErrorBuilder::message(
                sprintf("Test '%s' is marked as todo() but still has a closure body — the code will not execute.", $description)
            )
                ->identifier('pest.todoWithClosure')
                ->tip('Remove the closure body to keep it as a pending placeholder, use ->skip() to preserve the code but skip execution, or remove ->todo() to run the test.')
                ->build(),
        ];
    }

    /**
     * Walks up the method chain to find the original it()/test() FuncCall.
     */
    private function findTestCall(MethodCall $node): ?FuncCall
    {
        $current = $node->var;

        while ($current instanceof MethodCall) {
            $current = $current->var;
        }

        if ($current instanceof FuncCall && PestFunctionDetector::isTestFunction($current)) {
            return $current;
        }

        return null;
    }
}
