<?php

declare(strict_types=1);

namespace PestStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Detects duplicate test descriptions in the same file (top-level only).
 *
 * @implements Rule<FuncCall>
 */
final class DuplicateTestDescriptionRule implements Rule
{
    /** @var array<string, array<string, int>> Maps filename to description to line number */
    private array $seen = [];

    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    /**
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($scope->isInAnonymousFunction()) {
            return [];
        }

        if (! $node->name instanceof Name) {
            return [];
        }

        $funcName = $node->name->toString();
        if ($funcName !== 'it' && $funcName !== 'test') {
            return [];
        }

        $args = $node->getArgs();
        if ($args === [] || ! $args[0]->value instanceof String_) {
            return [];
        }

        $description = $args[0]->value->value;

        if ($funcName === 'it') {
            $description = 'it '.$description;
        }

        $file = $scope->getFile();

        if (! isset($this->seen[$file])) {
            $this->seen[$file] = [];
        }

        if (isset($this->seen[$file][$description])) {
            return [
                RuleErrorBuilder::message(
                    sprintf("A test with the description '%s' already exists in this file.", $description)
                )
                    ->identifier('pest.duplicateTestDescription')
                    ->build(),
            ];
        }

        $this->seen[$file][$description] = $node->getStartLine();

        return [];
    }
}
