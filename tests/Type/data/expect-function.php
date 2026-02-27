<?php

declare(strict_types=1);

namespace ExpectFunction;

use function PHPStan\Testing\assertType;

use stdClass;

function testExpectWithStringLiteral(): void
{
    assertType("Pest\Expectation<'hello'>", expect('hello'));
}

function testExpectWithIntLiteral(): void
{
    assertType('Pest\Expectation<42>', expect(42));
}

function testExpectWithEmptyArray(): void
{
    assertType('Pest\Expectation<array{}>', expect([]));
}

function testExpectWithShapedArray(): void
{
    assertType("Pest\Expectation<array{id: 1, name: 'test'}>", expect(['id' => 1, 'name' => 'test']));
}

function testExpectWithStringVariable(): void
{
    /** @var string $string */
    $string = 'test';
    assertType('Pest\Expectation<string>', expect($string));
}

function testExpectWithIntVariable(): void
{
    /** @var int $number */
    $number = 123;
    assertType('Pest\Expectation<int>', expect($number));
}

function testExpectWithNoArgs(): void
{
    assertType('Pest\Expectation<null>', expect());
}

function testExpectWithMixedType(): void
{
    /** @var int|string $mixed */
    $mixed = 'value';
    assertType('Pest\Expectation<int|string>', expect($mixed));
}

function testExpectWithObject(): void
{
    $obj = new stdClass;
    assertType('Pest\Expectation<stdClass>', expect($obj));
}

function testExpectWithBool(): void
{
    assertType('Pest\Expectation<true>', expect(true));
    assertType('Pest\Expectation<false>', expect(false));
}

function testExpectWithFloat(): void
{
    assertType('Pest\Expectation<3.14>', expect(3.14));
}

function testExpectWithNull(): void
{
    assertType('Pest\Expectation<null>', expect());
}
