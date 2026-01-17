<?php

declare(strict_types=1);

namespace TestClosures;

use function PHPStan\Testing\assertType;

use PHPUnit\Framework\TestCase;

// ============================================================================
// Default TestCase Tests
// ============================================================================

/**
 * Test that $this is correctly typed as TestCase in it() function.
 */
function testThisTypeInIt(): void
{
    it('has correct $this type', function (): void {
        assertType(TestCase::class, $this);
    });
}

/**
 * Test that $this is correctly typed as TestCase in test() function.
 */
function testThisTypeInTest(): void
{
    test('has correct $this type', function (): void {
        assertType(TestCase::class, $this);
    });
}

/**
 * Test that $this is correctly typed in beforeEach() hook.
 */
function testThisTypeInBeforeEach(): void
{
    beforeEach(function (): void {
        assertType(TestCase::class, $this);
    });
}

/**
 * Test that $this is correctly typed in afterEach() hook.
 */
function testThisTypeInAfterEach(): void
{
    afterEach(function (): void {
        assertType(TestCase::class, $this);
    });
}