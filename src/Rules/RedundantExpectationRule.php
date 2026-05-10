<?php

declare(strict_types=1);

namespace PestStan\Rules;

use PestStan\Analysis\Expectation\ExpectationSemanticAnalyzer;
use PestStan\Diagnostics\PestDiagnostic;
use PestStan\Diagnostics\PestDiagnostics;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;

/**
 * Detects type assertion expectations that will always pass (redundant assertions).
 *
 * @implements Rule<MethodCall>
 */
final class RedundantExpectationRule implements Rule
{
    private readonly ExpectationSemanticAnalyzer $semanticAnalyzer;

    public function __construct(?ExpectationSemanticAnalyzer $semanticAnalyzer = null)
    {
        $this->semanticAnalyzer = $semanticAnalyzer ?? new ExpectationSemanticAnalyzer;
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $diagnostic = $this->semanticAnalyzer->analyzeRedundantExpectation($node, $scope);
        if (! $diagnostic instanceof PestDiagnostic) {
            return [];
        }

        return [PestDiagnostics::toRuleError($diagnostic)];
    }
}
