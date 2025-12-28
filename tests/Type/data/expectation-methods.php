<?php

declare(strict_types=1);

namespace ExpectationMethods;

use function PHPStan\Testing\assertType;

function test(): void
{
    // Test that expect() maintains its type - this is what our extension does
    assertType('Pest\Expectation<string|null>', expect('test'));
    assertType('Pest\Expectation<int|null>', expect(42));
    assertType('Pest\Expectation<array{}|null>', expect([]));
}
