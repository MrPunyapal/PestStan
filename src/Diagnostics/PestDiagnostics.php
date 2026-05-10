<?php

declare(strict_types=1);

namespace PestStan\Diagnostics;

use PestStan\Analysis\Expectation\ExpectationMatcherRegistry;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\RuleErrorBuilder;

final class PestDiagnostics
{
    public static function invalidMatcherType(string $matcher, string $valueType, string $requirement): PestDiagnostic
    {
        return new PestDiagnostic(
            kind: 'invalid_matcher_type',
            identifier: self::identifierForRequirement($requirement),
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
            matcher: $matcher,
            valueType: $valueType,
            requirement: $requirement,
        );
    }

    public static function impossibleExpectation(string $matcher, string $valueType): PestDiagnostic
    {
        return new PestDiagnostic(
            kind: 'impossible_expectation',
            identifier: 'pest.impossibleExpectation',
            message: sprintf('Calling %s() on Expectation<%s>; assertion is impossible.', $matcher, $valueType),
            tip: sprintf('The expectation value is %s, which can never satisfy %s().', $valueType, $matcher),
            matcher: $matcher,
            valueType: $valueType,
        );
    }

    public static function redundantExpectation(string $matcher, string $valueType): PestDiagnostic
    {
        return new PestDiagnostic(
            kind: 'redundant_expectation',
            identifier: 'pest.redundantExpectation',
            message: sprintf('Calling %s() on Expectation<%s>; assertion is redundant.', $matcher, $valueType),
            tip: sprintf('The expectation value is already guaranteed to satisfy %s().', $matcher),
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
            message: sprintf('%s() runs in static context — $this is not available. Use %s() instead.', $hook, $replacementHook),
            line: $line,
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
            ExpectationMatcherRegistry::REQUIREMENT_STRING => 'pest.expectationRequiresString',
            ExpectationMatcherRegistry::REQUIREMENT_ITERABLE => 'pest.expectationRequiresIterable',
            ExpectationMatcherRegistry::REQUIREMENT_COUNTABLE_OR_ITERABLE => 'pest.expectationRequiresCountableOrIterable',
            default => 'pest.invalidMatcherType',
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
