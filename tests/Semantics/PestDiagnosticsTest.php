<?php

declare(strict_types=1);

namespace Tests\Semantics;

use PestStan\Analysis\Expectation\ExpectationMatcherRegistry;
use PestStan\Analysis\Expectation\MatcherCategoryRegistry;
use PestStan\Analysis\Expectation\MatcherRequirementRegistry;
use PestStan\Diagnostics\PestDiagnosticIdentifiers;
use PestStan\Diagnostics\PestDiagnostics;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

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
        $identifiers = array_values((new ReflectionClass(PestDiagnosticIdentifiers::class))->getConstants());

        self::assertCount(count(array_unique($identifiers)), $identifiers);

        foreach ($identifiers as $identifier) {
            self::assertMatchesRegularExpression('/^pest(?:\.[a-z][A-Za-z0-9]*)+$/', $identifier);
        }
    }
}
