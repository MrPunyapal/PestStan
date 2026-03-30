<?php

declare(strict_types=1);

namespace PestStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Detects beforeAll() and afterAll() calls inside describe() blocks.
 *
 * @implements Rule<FuncCall>
 */
final class DisallowedCallInDescribeRule implements Rule
{
    /** @var array<string, string> */
    private const FORBIDDEN_FUNCTIONS = [
        'beforeAll' => 'pest.beforeAllInDescribe',
        'afterAll' => 'pest.afterAllInDescribe',
    ];

    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    /**
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->name instanceof Name || $node->name->toString() !== 'describe') {
            return [];
        }

        $args = $node->getArgs();
        if (count($args) < 2) {
            return [];
        }

        $closure = $args[1]->value;
        if (! $closure instanceof Closure) {
            return [];
        }

        $errors = [];

        foreach ($closure->stmts as $stmt) {
            if (! $stmt instanceof Expression || ! $stmt->expr instanceof FuncCall) {
                continue;
            }

            $call = $stmt->expr;
            if (! $call->name instanceof Name) {
                continue;
            }

            $name = $call->name->toString();
            if (! isset(self::FORBIDDEN_FUNCTIONS[$name])) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(
                sprintf('%s() cannot be used inside describe() blocks.', $name)
            )
                ->identifier(self::FORBIDDEN_FUNCTIONS[$name])
                ->line($call->getStartLine())
                ->build();
        }

        return $errors;
    }
}
