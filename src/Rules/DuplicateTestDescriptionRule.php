<?php

declare(strict_types=1);

namespace PestStan\Rules;

use PestStan\Diagnostics\PestDiagnosticIdentifiers;
use PestStan\PestFunctionDetector;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
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
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($scope->isInAnonymousFunction()) {
            return [];
        }

        if (! PestFunctionDetector::isTestFunction($node)) {
            return [];
        }

        $description = PestFunctionDetector::extractDescription($node);
        if ($description === null) {
            return [];
        }

        if (! $node->name instanceof Name) {
            return [];
        }

        $funcName = $node->name->toString();
        if ($funcName === 'it') {
            $description = 'it ' . $description;
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
                    ->identifier(PestDiagnosticIdentifiers::TEST_DUPLICATE_DESCRIPTION)
                    ->build(),
            ];
        }

        $this->seen[$file][$description] = $node->getStartLine();

        return [];
    }
}
