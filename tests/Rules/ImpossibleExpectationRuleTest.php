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
            'Calling toBeString() on Expectation<int> will always fail.',
            7,
            'The value is of type int, which can never satisfy toBeString().',
        ],
        [
            'Calling toBeInt() on Expectation<string> will always fail.',
            11,
            'The value is of type string, which can never satisfy toBeInt().',
        ],
        [
            'Calling toBeFloat() on Expectation<string> will always fail.',
            15,
            'The value is of type string, which can never satisfy toBeFloat().',
        ],
        [
            'Calling toBeBool() on Expectation<string> will always fail.',
            19,
            'The value is of type string, which can never satisfy toBeBool().',
        ],
        [
            'Calling toBeTrue() on Expectation<int> will always fail.',
            23,
            'The value is of type int, which can never satisfy toBeTrue().',
        ],
        [
            'Calling toBeFalse() on Expectation<int> will always fail.',
            27,
            'The value is of type int, which can never satisfy toBeFalse().',
        ],
        [
            'Calling toBeNull() on Expectation<string> will always fail.',
            31,
            'The value is of type string, which can never satisfy toBeNull().',
        ],
        [
            'Calling toBeArray() on Expectation<string> will always fail.',
            35,
            'The value is of type string, which can never satisfy toBeArray().',
        ],
        [
            'Calling toBeObject() on Expectation<int> will always fail.',
            39,
            'The value is of type int, which can never satisfy toBeObject().',
        ],
        [
            'Calling toBeIterable() on Expectation<int> will always fail.',
            43,
            'The value is of type int, which can never satisfy toBeIterable().',
        ],
        [
            'Calling toBeCallable() on Expectation<null> will always fail.',
            47,
            'The value is of type null, which can never satisfy toBeCallable().',
        ],
        [
            'Calling toBeInstanceOf() on Expectation<int> will always fail.',
            51,
            'The value is of type int, which can never satisfy toBeInstanceOf().',
        ],
        [
            'Calling toBeScalar() on Expectation<array> will always fail.',
            55,
            'The value is of type array, which can never satisfy toBeScalar().',
        ],
        [
            'Calling toBeNumeric() on Expectation<null> will always fail.',
            59,
            'The value is of type null, which can never satisfy toBeNumeric().',
        ],
        [
            'Calling toBeInt() on Expectation<string> will always fail.',
            74,
            'The value is of type string, which can never satisfy toBeInt().',
        ],
        [
            'Calling toBeInt() on Expectation<string> will always fail.',
            80,
            'The value is of type string, which can never satisfy toBeInt().',
        ],
    ]);
});
