<?php

declare(strict_types=1);

namespace PestStan\Diagnostics;

use PestStan\Analysis\Expectation\ExpectationMatcherRegistry;
use PestStan\Analysis\Expectation\MatcherCategoryRegistry;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\RuleErrorBuilder;

final class PestDiagnostics
{
    public static function invalidMatcherType(string $matcher, string $valueType, string $requirement): PestDiagnostic
    {
        return new PestDiagnostic(
            kind: 'invalid_matcher_type',
            identifier: self::identifierForRequirement($requirement),
            severity: 'error',
            fixable: false,
            message: sprintf(
                'Calling %s() on Expectation<%s>; matcher requires %s.',
                $matcher,
                $valueType,
                self::requirementLabel($requirement),
            ),
            tip: sprintf(
                'Pass %s value to expect() before calling %s().',
                self::requirementValuePhrase($requirement),
                $matcher,
            ),
            semanticCategory: self::categoryForRequirement($requirement),
            suggestedFix: sprintf('Pass %s to expect() before calling %s().', self::requirementValuePhrase($requirement), $matcher),
            relatedMatcher: $matcher,
            expectedType: self::requirementLabel($requirement),
            actualType: $valueType,
            matcher: $matcher,
            valueType: $valueType,
            requirement: $requirement,
        );
    }

    public static function impossibleExpectation(string $matcher, string $valueType): PestDiagnostic
    {
        return new PestDiagnostic(
            kind: 'impossible_expectation',
            identifier: PestDiagnosticIdentifiers::EXPECTATION_IMPOSSIBLE,
            severity: 'error',
            fixable: false,
            message: sprintf('Calling %s() on Expectation<%s>; assertion is impossible.', $matcher, $valueType),
            tip: sprintf('The expectation value is %s, which can never satisfy %s().', $valueType, $matcher),
            semanticCategory: MatcherCategoryRegistry::SEMANTIC_ASSERTION,
            suggestedFix: sprintf('Remove %s() or change the expectation value type before asserting.', $matcher),
            relatedMatcher: $matcher,
            expectedType: 'compatible assertion type',
            actualType: $valueType,
            matcher: $matcher,
            valueType: $valueType,
        );
    }

    public static function redundantExpectation(string $matcher, string $valueType): PestDiagnostic
    {
        return new PestDiagnostic(
            kind: 'redundant_expectation',
            identifier: PestDiagnosticIdentifiers::EXPECTATION_REDUNDANT,
            severity: 'warning',
            fixable: true,
            message: sprintf('Calling %s() on Expectation<%s>; assertion is redundant.', $matcher, $valueType),
            tip: sprintf('The expectation value is already guaranteed to satisfy %s().', $matcher),
            semanticCategory: MatcherCategoryRegistry::STATE_ASSERTION,
            suggestedFix: sprintf('Remove the redundant %s() assertion.', $matcher),
            relatedMatcher: $matcher,
            expectedType: 'already satisfied',
            actualType: $valueType,
            matcher: $matcher,
            valueType: $valueType,
        );
    }

    public static function invalidLifecycleThisUsage(
        string $hook,
        string $replacementHook,
        string $identifier,
        int $line,
    ): PestDiagnostic {
        return new PestDiagnostic(
            kind: 'invalid_lifecycle_usage',
            identifier: $identifier,
            severity: 'error',
            fixable: true,
            message: sprintf('%s() runs in static context — $this is not available. Use %s() instead.', $hook, $replacementHook),
            line: $line,
            semanticCategory: 'lifecycle',
            suggestedFix: sprintf('Replace %s() with %s() when using $this.', $hook, $replacementHook),
            expectedType: 'instance context',
            actualType: 'static context',
            lifecycleHook: $hook,
        );
    }

    public static function toRuleError(PestDiagnostic $diagnostic): IdentifierRuleError
    {
        $builder = RuleErrorBuilder::message($diagnostic->message)
            ->identifier($diagnostic->identifier);

        if ($diagnostic->tip !== null) {
            $builder->tip($diagnostic->tip);
        }

        if ($diagnostic->line !== null) {
            $builder->line($diagnostic->line);
        }

        return $builder->build();
    }

    private static function identifierForRequirement(string $requirement): string
    {
        return match ($requirement) {
            ExpectationMatcherRegistry::REQUIREMENT_STRING => PestDiagnosticIdentifiers::EXPECTATION_REQUIRES_STRING,
            ExpectationMatcherRegistry::REQUIREMENT_ITERABLE => PestDiagnosticIdentifiers::EXPECTATION_REQUIRES_ITERABLE,
            ExpectationMatcherRegistry::REQUIREMENT_COUNTABLE_OR_ITERABLE => PestDiagnosticIdentifiers::EXPECTATION_REQUIRES_COUNTABLE_OR_ITERABLE,
            default => PestDiagnosticIdentifiers::EXPECTATION_REQUIRES_STRING,
        };
    }

    private static function categoryForRequirement(string $requirement): string
    {
        return match ($requirement) {
            ExpectationMatcherRegistry::REQUIREMENT_STRING => MatcherCategoryRegistry::STRING,
            ExpectationMatcherRegistry::REQUIREMENT_ITERABLE,
            ExpectationMatcherRegistry::REQUIREMENT_COUNTABLE_OR_ITERABLE => MatcherCategoryRegistry::ITERABLE,
            default => MatcherCategoryRegistry::SEMANTIC_ASSERTION,
        };
    }

    private static function requirementLabel(string $requirement): string
    {
        return match ($requirement) {
            ExpectationMatcherRegistry::REQUIREMENT_COUNTABLE_OR_ITERABLE => 'countable or iterable',
            default => $requirement,
        };
    }

    private static function requirementValuePhrase(string $requirement): string
    {
        return match ($requirement) {
            ExpectationMatcherRegistry::REQUIREMENT_STRING => 'a string',
            ExpectationMatcherRegistry::REQUIREMENT_ITERABLE => 'an iterable',
            ExpectationMatcherRegistry::REQUIREMENT_COUNTABLE_OR_ITERABLE => 'a countable or iterable',
            default => 'a compatible',
        };
    }
}
