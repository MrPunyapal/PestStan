<?php

declare(strict_types=1);

namespace Tests\Semantics;

use JsonException;
use PestStan\Analysis\Expectation\ExpectationMatcherRegistry;
use PestStan\Analysis\Expectation\MatcherCategoryRegistry;
use PestStan\Analysis\Expectation\MatcherRequirementRegistry;
use PestStan\Diagnostics\PestDiagnostic;
use PestStan\Diagnostics\PestDiagnosticIdentifiers;
use PestStan\Diagnostics\PestDiagnostics;
use PHPUnit\Framework\TestCase;

final class PestDiagnosticsTest extends TestCase
{
    public function test_invalid_matcher_diagnostics_expose_canonical_metadata(): void
    {
        $diagnostic = PestDiagnostics::invalidMatcherType('toBeAlpha', 'int', MatcherRequirementRegistry::STRING);

        self::assertSame(PestDiagnosticIdentifiers::EXPECTATION_REQUIRES_STRING, $diagnostic->identifier);
        self::assertSame('error', $diagnostic->severity);
        self::assertFalse($diagnostic->fixable);
        self::assertSame(MatcherCategoryRegistry::STRING, $diagnostic->semanticCategory);
        self::assertSame('high', $diagnostic->confidenceLevel);
        self::assertSame('adjust_input_type', $diagnostic->fixStrategy);
        self::assertSame('pest.expectation.adjustInputType', $diagnostic->fixRule);
        self::assertSame('expectation.requires_string', $diagnostic->semanticCode);
        self::assertSame(MatcherCategoryRegistry::STRING, $diagnostic->matcherCategory);
        self::assertSame('toBeAlpha', $diagnostic->relatedMatcher);
        self::assertSame('string', $diagnostic->expectedType);
        self::assertSame('int', $diagnostic->actualType);
    }

    public function test_matcher_metadata_is_cached_and_categorized(): void
    {
        $registry = new ExpectationMatcherRegistry;

        $first = $registry->metadataFor('toBeString');
        $second = $registry->metadataFor('toBeString');

        self::assertNotNull($first);
        self::assertSame($first, $second);
        self::assertSame('toBeString', $first->methodName);
        self::assertSame('string', $first->assertion);
        self::assertContains(MatcherCategoryRegistry::TYPE_ASSERTION, $first->categories);
        self::assertContains(MatcherCategoryRegistry::SEMANTIC_ASSERTION, $first->categories);
        self::assertContains(MatcherCategoryRegistry::STATE_ASSERTION, $first->categories);

        $collection = $registry->metadataFor('toHaveCount');

        self::assertNotNull($collection);
        self::assertContains(MatcherCategoryRegistry::COLLECTION, $collection->categories);
        self::assertContains(MatcherCategoryRegistry::ITERABLE, $collection->categories);
    }

    public function test_lifecycle_diagnostics_expose_canonical_identifiers(): void
    {
        $diagnostic = PestDiagnostics::invalidLifecycleThisUsage(
            'beforeAll',
            'beforeEach',
            PestDiagnosticIdentifiers::LIFECYCLE_BEFORE_ALL_THIS_USAGE,
            12,
        );

        self::assertSame(PestDiagnosticIdentifiers::LIFECYCLE_BEFORE_ALL_THIS_USAGE, $diagnostic->identifier);
        self::assertSame('error', $diagnostic->severity);
        self::assertTrue($diagnostic->fixable);
        self::assertSame('lifecycle', $diagnostic->semanticCategory);
        self::assertSame('high', $diagnostic->confidenceLevel);
        self::assertSame('replace_hook', $diagnostic->fixStrategy);
        self::assertSame('pest.lifecycle.replaceStaticHook', $diagnostic->fixRule);
        self::assertSame('lifecycle.before_all_this_usage', $diagnostic->semanticCode);
        self::assertNull($diagnostic->matcherCategory);
        self::assertSame('static context', $diagnostic->actualType);
    }

    public function test_redundant_diagnostics_expose_machine_readable_metadata(): void
    {
        $diagnostic = PestDiagnostics::redundantExpectation('toBeString', 'string');

        self::assertSame('warning', $diagnostic->severity);
        self::assertTrue($diagnostic->fixable);
        self::assertSame('high', $diagnostic->confidenceLevel);
        self::assertSame('remove_redundant_assertion', $diagnostic->fixStrategy);
        self::assertSame('pest.expectation.removeRedundantAssertion', $diagnostic->fixRule);
        self::assertSame('expectation.redundant', $diagnostic->semanticCode);
        self::assertSame(MatcherCategoryRegistry::TYPE_ASSERTION, $diagnostic->matcherCategory);
    }

    public function test_identifier_constants_are_unique_and_canonical(): void
    {
        $identifiers = PestDiagnosticIdentifiers::all();

        self::assertCount(count(array_unique($identifiers)), $identifiers);

        foreach ($identifiers as $identifier) {
            self::assertTrue(PestDiagnosticIdentifiers::isCanonical($identifier));
            self::assertMatchesRegularExpression('/^pest(?:\.[a-z][A-Za-z0-9]*)+$/', $identifier);
        }
    }

    public function test_legacy_aliases_resolve_to_canonical_identifiers(): void
    {
        self::assertSame(
            PestDiagnosticIdentifiers::REPEAT_INVALID_VALUE,
            PestDiagnosticIdentifiers::canonicalize('pest.repeatInvalidValue'),
        );
        self::assertSame(
            PestDiagnosticIdentifiers::REPEAT_INVALID_VALUE,
            PestDiagnosticIdentifiers::canonicalize('pest.repeat.invalidValue'),
        );
        self::assertSame(
            PestDiagnosticIdentifiers::DESCRIBE_BEFORE_ALL_DISALLOWED,
            PestDiagnosticIdentifiers::canonicalize('pest.beforeAllInDescribe'),
        );
        self::assertContains(
            'pest.describe.beforeAllDisallowed',
            PestDiagnosticIdentifiers::aliasesFor(PestDiagnosticIdentifiers::DESCRIBE_BEFORE_ALL_DISALLOWED),
        );
        self::assertContains(
            'pest.repeatInvalidValue',
            PestDiagnosticIdentifiers::aliasesFor(PestDiagnosticIdentifiers::REPEAT_INVALID_VALUE),
        );
    }

    /**
     * @throws JsonException
     */
    public function test_diagnostic_serialization_is_stable_and_json_safe(): void
    {
        $diagnostic = PestDiagnostics::invalidMatcherType('toBeAlpha', 'int', MatcherRequirementRegistry::STRING);

        self::assertSame([
            'kind' => 'invalid_matcher_type',
            'identifier' => PestDiagnosticIdentifiers::EXPECTATION_REQUIRES_STRING,
            'severity' => 'error',
            'fixable' => false,
            'message' => 'Calling toBeAlpha() on Expectation<int>; matcher requires string.',
            'tip' => 'Pass a string value to expect() before calling toBeAlpha().',
            'line' => null,
            'semanticCategory' => MatcherCategoryRegistry::STRING,
            'confidenceLevel' => 'high',
            'fixStrategy' => 'adjust_input_type',
            'fixRule' => 'pest.expectation.adjustInputType',
            'semanticCode' => 'expectation.requires_string',
            'matcherCategory' => MatcherCategoryRegistry::STRING,
            'suggestedFix' => 'Pass a string to expect() before calling toBeAlpha().',
            'relatedMatcher' => 'toBeAlpha',
            'expectedType' => 'string',
            'actualType' => 'int',
            'matcher' => 'toBeAlpha',
            'valueType' => 'int',
            'requirement' => MatcherRequirementRegistry::STRING,
            'lifecycleHook' => null,
        ], $diagnostic->toArray());

        self::assertSame(
            json_encode($diagnostic->toArray(), JSON_THROW_ON_ERROR),
            json_encode($diagnostic, JSON_THROW_ON_ERROR),
        );
    }

    public function test_diagnostic_serialization_canonicalizes_alias_identifiers(): void
    {
        $diagnostic = new PestDiagnostic(
            kind: 'invalid_lifecycle_usage',
            identifier: 'pest.beforeAllInDescribe',
            severity: 'error',
            fixable: true,
            message: 'beforeAll() cannot be used inside describe() blocks.',
        );

        self::assertSame(
            PestDiagnosticIdentifiers::DESCRIBE_BEFORE_ALL_DISALLOWED,
            $diagnostic->toArray()['identifier'],
        );
    }
}
