<?php

declare(strict_types=1);

namespace Tests\Rules;

use PestStan\Rules\ImpossibleExpectationRule;
use Tests\RuleTestCase;

beforeAll(function (): void {
    RuleTestCase::$rule = new ImpossibleExpectationRule;
    RuleTestCase::$additionalConfigFiles = [
        __DIR__ . '/../extension.neon',
    ];
});

test('impossible expectations are reported', function (): void {
    $this->analyse([
        __DIR__ . '/data/impossible-expectation.php',
    ], [
        [
            'Calling toBeString() on Expectation<int>; assertion is impossible.',
            7,
            'The expectation value is int, which can never satisfy toBeString().',
        ],
        [
            'Calling toBeInt() on Expectation<string>; assertion is impossible.',
            11,
            'The expectation value is string, which can never satisfy toBeInt().',
        ],
        [
            'Calling toBeFloat() on Expectation<string>; assertion is impossible.',
            15,
            'The expectation value is string, which can never satisfy toBeFloat().',
        ],
        [
            'Calling toBeBool() on Expectation<string>; assertion is impossible.',
            19,
            'The expectation value is string, which can never satisfy toBeBool().',
        ],
        [
            'Calling toBeTrue() on Expectation<int>; assertion is impossible.',
            23,
            'The expectation value is int, which can never satisfy toBeTrue().',
        ],
        [
            'Calling toBeFalse() on Expectation<int>; assertion is impossible.',
            27,
            'The expectation value is int, which can never satisfy toBeFalse().',
        ],
        [
            'Calling toBeNull() on Expectation<string>; assertion is impossible.',
            31,
            'The expectation value is string, which can never satisfy toBeNull().',
        ],
        [
            'Calling toBeArray() on Expectation<string>; assertion is impossible.',
            35,
            'The expectation value is string, which can never satisfy toBeArray().',
        ],
        [
            'Calling toBeObject() on Expectation<int>; assertion is impossible.',
            39,
            'The expectation value is int, which can never satisfy toBeObject().',
        ],
        [
            'Calling toBeIterable() on Expectation<int>; assertion is impossible.',
            43,
            'The expectation value is int, which can never satisfy toBeIterable().',
        ],
        [
            'Calling toBeCallable() on Expectation<null>; assertion is impossible.',
            47,
            'The expectation value is null, which can never satisfy toBeCallable().',
        ],
        [
            'Calling toBeInstanceOf() on Expectation<int>; assertion is impossible.',
            51,
            'The expectation value is int, which can never satisfy toBeInstanceOf().',
        ],
        [
            'Calling toBeScalar() on Expectation<array>; assertion is impossible.',
            55,
            'The expectation value is array, which can never satisfy toBeScalar().',
        ],
        [
            'Calling toBeNumeric() on Expectation<null>; assertion is impossible.',
            59,
            'The expectation value is null, which can never satisfy toBeNumeric().',
        ],
        [
            'Calling toBeInt() on Expectation<string>; assertion is impossible.',
            74,
            'The expectation value is string, which can never satisfy toBeInt().',
        ],
        [
            'Calling toBeInt() on Expectation<string>; assertion is impossible.',
            80,
            'The expectation value is string, which can never satisfy toBeInt().',
        ],
    ]);
});

test('impossible chains only report the first broken step', function (): void {
    $this->analyse([
        __DIR__ . '/data/impossible-expectation-chain.php',
    ], [
        [
            'Calling toBeString() on Expectation<int>; assertion is impossible.',
            6,
            'The expectation value is int, which can never satisfy toBeString().',
        ],
    ]);
});

test('instanceof and numeric impossibilities are reported conservatively', function (): void {
    $this->analyse([
        __DIR__ . '/data/impossible-expectation-instanceof.php',
    ], [
        [
            'Calling toBeInstanceOf() on Expectation<stdClass>; assertion is impossible.',
            12,
            'The expectation value is stdClass, which can never satisfy toBeInstanceOf().',
        ],
        [
            'Calling toBeInstanceOf() on Expectation<RuntimeException>; assertion is impossible.',
            16,
            'The expectation value is RuntimeException, which can never satisfy toBeInstanceOf().',
        ],
        [
            'Calling toBeNumeric() on Expectation<bool>; assertion is impossible.',
            23,
            'The expectation value is bool, which can never satisfy toBeNumeric().',
        ],
    ]);
});

test('impossible semantic chains suppress downstream matcher diagnostics', function (): void {
    $this->analyse([
        __DIR__ . '/data/impossible-expectation-semantic-chain.php',
    ], [
        [
            'Calling toBeString() on Expectation<int>; assertion is impossible.',
            6,
            'The expectation value is int, which can never satisfy toBeString().',
        ],
        [
            'Calling toBeString() on Expectation<int>; assertion is impossible.',
            10,
            'The expectation value is int, which can never satisfy toBeString().',
        ],
    ]);
});
