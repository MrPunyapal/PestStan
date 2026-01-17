<?php

declare(strict_types=1);

namespace ExpectationMethods;

use function PHPStan\Testing\assertType;

/**
 * Test that expect() returns proper Expectation<TValue> type
 */
function testExpectReturnType(): void
{
    assertType('Pest\Expectation<string|null>', expect('test'));
    assertType('Pest\Expectation<int|null>', expect(42));
    assertType('Pest\Expectation<array{}|null>', expect([]));
}

/**
 * Test that the Expectation class has a typed $value property
 */
function testExpectationValueProperty(): void
{
    $expectation = expect('hello');
    assertType('string|null', $expectation->value);
}
