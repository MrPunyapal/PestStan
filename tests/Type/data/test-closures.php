<?php

declare(strict_types=1);

namespace TestClosures;

use function PHPStan\Testing\assertType;

use PHPUnit\Framework\TestCase;

function testClosures(): void
{
    // Test that $this is bound to TestCase in test functions
    it('has correct $this type', function (): void {
        assertType(TestCase::class, $this);
    });

    test('also has correct $this type', function (): void {
        assertType(TestCase::class, $this);
    });

    // Test hooks
    beforeEach(function (): void {
        assertType(TestCase::class, $this);
    });

    afterEach(function (): void {
        assertType(TestCase::class, $this);
    });
}
