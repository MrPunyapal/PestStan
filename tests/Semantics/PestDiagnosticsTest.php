<?php

declare(strict_types=1);

namespace Tests\Semantics;

use PestStan\Analysis\Expectation\ExpectationMatcherRegistry;
use PestStan\Analysis\Expectation\MatcherCategoryRegistry;
use PestStan\Analysis\Expectation\MatcherRequirementRegistry;
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
        self::assertContains(MatcherCategoryRegistry::SEMANTIC_ASSERTION, $first->categories);
        self::assertContains(MatcherCategoryRegistry::STATE_ASSERTION, $first->categories);
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
        self::assertSame('static context', $diagnostic->actualType);
    }
}
