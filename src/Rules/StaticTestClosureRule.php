<?php

declare(strict_types=1);

namespace PestStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Detects static closures passed to Pest test functions.
 *
 * @implements Rule<FuncCall>
 */
final class StaticTestClosureRule implements Rule
{
    /** @var array<string, int> Maps function names to the closure argument index */
    private const PEST_FUNCTIONS = [
        'it' => 1,
        'test' => 1,
        'describe' => 1,
        'beforeEach' => 0,
        'afterEach' => 0,
        'beforeAll' => 0,
        'afterAll' => 0,
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
        if (! $node->name instanceof Name) {
            return [];
        }

        $name = $node->name->toString();
        if (! isset(self::PEST_FUNCTIONS[$name])) {
            return [];
        }

        $closureArgIndex = self::PEST_FUNCTIONS[$name];
        $args = $node->getArgs();
        if (! isset($args[$closureArgIndex])) {
            return [];
        }

        $closure = $args[$closureArgIndex]->value;
        if (! $closure instanceof Closure && ! $closure instanceof ArrowFunction) {
            return [];
        }

        if (! $closure->static) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf('Test closure passed to %s() must not be static.', $name)
            )
                ->identifier('pest.staticTestClosure')
                ->build(),
        ];
    }
}
