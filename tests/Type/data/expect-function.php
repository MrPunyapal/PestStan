<?php

declare(strict_types=1);

namespace ExpectFunction;

use function PHPStan\Testing\assertType;

use stdClass;

function test(): void
{
    // Test that expect() returns generic Expectation<TValue>
    assertType('Pest\Expectation<string|null>', expect('hello'));
    assertType('Pest\Expectation<int|null>', expect(42));
    assertType('Pest\Expectation<array{}|null>', expect([]));
    assertType('Pest\Expectation<array{id: int, name: string}|null>', expect(['id' => 1, 'name' => 'test']));

    // Test with variables
    $string = 'test';
    assertType('Pest\Expectation<string|null>', expect($string));

    $number = 123;
    assertType('Pest\Expectation<int|null>', expect($number));

    // Test with null
    assertType('Pest\Expectation<null>', expect());

    // Test with mixed types
    /** @var int|string $mixed */
    $mixed = 'value';
    assertType('Pest\Expectation<int|string|null>', expect($mixed));

    // Test with objects
    $obj = new stdClass();
    assertType('Pest\Expectation<stdClass|null>', expect($obj));
}
