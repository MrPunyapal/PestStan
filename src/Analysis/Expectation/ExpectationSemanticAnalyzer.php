<?php

declare(strict_types=1);

namespace PestStan\Analysis\Expectation;

use PestStan\Diagnostics\PestDiagnostic;
use PestStan\Diagnostics\PestDiagnostics;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Type\VerbosityLevel;

final class ExpectationSemanticAnalyzer
{
    private readonly ExpectationChainStateResolver $chainStateResolver;

    public function __construct(?ExpectationChainStateResolver $chainStateResolver = null)
    {
        $this->chainStateResolver = $chainStateResolver ?? new ExpectationChainStateResolver;
    }

    public function analyzeInvalidMatcherType(MethodCall $methodCall, Scope $scope): ?PestDiagnostic
    {
        $state = $this->chainStateResolver->resolve($methodCall, $scope);
        if (! $state instanceof ExpectationChainState || ! $state->stepResult instanceof ExpectationAssertionResult) {
            return null;
        }

        if (! $state->stepResult->isInvalidRequirement() || ! $state->stepResult->metadata instanceof MatcherSemanticMetadata) {
            return null;
        }

        if ($state->stepResult->metadata->requirement === null) {
            return null;
        }

        return PestDiagnostics::invalidMatcherType(
            $state->stepResult->methodName,
            $state->stepResult->incomingValueType->describe(VerbosityLevel::typeOnly()),
            $state->stepResult->metadata->requirement,
        );
    }

    public function analyzeImpossibleExpectation(MethodCall $methodCall, Scope $scope): ?PestDiagnostic
    {
        $state = $this->chainStateResolver->resolve($methodCall, $scope);
        if (! $state instanceof ExpectationChainState || ! $state->stepResult instanceof ExpectationAssertionResult) {
            return null;
        }

        if (! $state->stepResult->isImpossible()) {
            return null;
        }

        return PestDiagnostics::impossibleExpectation(
            $state->stepResult->methodName,
            $state->stepResult->incomingValueType->describe(VerbosityLevel::typeOnly()),
        );
    }

    public function analyzeRedundantExpectation(MethodCall $methodCall, Scope $scope): ?PestDiagnostic
    {
        $state = $this->chainStateResolver->resolve($methodCall, $scope);
        if (! $state instanceof ExpectationChainState || ! $state->stepResult instanceof ExpectationAssertionResult) {
            return null;
        }

        if (! $state->stepResult->isRedundant()) {
            return null;
        }

        if ($this->shouldSuppressRedundantExpectation($methodCall, $scope)) {
            return null;
        }

        return PestDiagnostics::redundantExpectation(
            $state->stepResult->methodName,
            $state->stepResult->incomingValueType->describe(VerbosityLevel::typeOnly()),
        );
    }

    private function shouldSuppressRedundantExpectation(MethodCall $methodCall, Scope $scope): bool
    {
        if (! $methodCall->var instanceof MethodCall) {
            return false;
        }

        $previousState = $this->chainStateResolver->resolve($methodCall->var, $scope);
        if (! $previousState instanceof ExpectationChainState || ! $previousState->stepResult instanceof ExpectationAssertionResult) {
            return false;
        }

        if (! $previousState->stepResult->metadata instanceof MatcherSemanticMetadata) {
            return false;
        }

        if ($previousState->stepResult->metadata->assertion === null) {
            return false;
        }

        return in_array(
            $previousState->stepResult->status,
            [ExpectationAssertionResult::ASSERTED, ExpectationAssertionResult::REDUNDANT],
            true,
        );
    }
}
