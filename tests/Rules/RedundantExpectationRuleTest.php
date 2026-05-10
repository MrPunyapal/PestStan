<?php

declare(strict_types=1);

namespace Tests\Rules;

use PestStan\Rules\RedundantExpectationRule;
use Tests\RuleTestCase;

beforeAll(function (): void {
    RuleTestCase::$rule = new RedundantExpectationRule;
    RuleTestCase::$additionalConfigFiles = [
        __DIR__ . '/../extension.neon',
    ];
});

test('redundant expectations are reported', function (): void {
    $this->analyse([
        __DIR__ . '/data/redundant-expectation.php',
    ], [
        [
            'Calling toBeTrue() on Expectation<true>; assertion is redundant.',
            7,
            'The expectation value is already guaranteed to satisfy toBeTrue().',
        ],
        [
            'Calling toBeFalse() on Expectation<false>; assertion is redundant.',
            11,
            'The expectation value is already guaranteed to satisfy toBeFalse().',
        ],
        [
            'Calling toBeBool() on Expectation<true>; assertion is redundant.',
            15,
            'The expectation value is already guaranteed to satisfy toBeBool().',
        ],
        [
            'Calling toBeString() on Expectation<string>; assertion is redundant.',
            19,
            'The expectation value is already guaranteed to satisfy toBeString().',
        ],
        [
            'Calling toBeInt() on Expectation<int>; assertion is redundant.',
            23,
            'The expectation value is already guaranteed to satisfy toBeInt().',
        ],
        [
            'Calling toBeFloat() on Expectation<float>; assertion is redundant.',
            27,
            'The expectation value is already guaranteed to satisfy toBeFloat().',
        ],
        [
            'Calling toBeNull() on Expectation<null>; assertion is redundant.',
            31,
            'The expectation value is already guaranteed to satisfy toBeNull().',
        ],
        [
            'Calling toBeArray() on Expectation<array>; assertion is redundant.',
            35,
            'The expectation value is already guaranteed to satisfy toBeArray().',
        ],
        [
            'Calling toBeScalar() on Expectation<string>; assertion is redundant.',
            39,
            'The expectation value is already guaranteed to satisfy toBeScalar().',
        ],
        [
            'Calling toBeNumeric() on Expectation<int>; assertion is redundant.',
            43,
            'The expectation value is already guaranteed to satisfy toBeNumeric().',
        ],
        [
            'Calling toBeInstanceOf() on Expectation<stdClass>; assertion is redundant.',
            47,
            'The expectation value is already guaranteed to satisfy toBeInstanceOf().',
        ],
    ]);
});

test('redundant chains ignore earlier invalid matcher steps', function (): void {
    $this->analyse([
        __DIR__ . '/data/redundant-expectation-chain.php',
    ], [
        [
            'Calling toBeString() on Expectation<string>; assertion is redundant.',
            10,
            'The expectation value is already guaranteed to satisfy toBeString().',
        ],
    ]);
});

test('inheritance and scalar redundancies are reported', function (): void {
    $this->analyse([
        __DIR__ . '/data/redundant-expectation-instanceof.php',
    ], [
        [
            'Calling toBeInstanceOf() on Expectation<RuntimeException>; assertion is redundant.',
            6,
            'The expectation value is already guaranteed to satisfy toBeInstanceOf().',
        ],
        [
            'Calling toBeInstanceOf() on Expectation<RuntimeException>; assertion is redundant.',
            10,
            'The expectation value is already guaranteed to satisfy toBeInstanceOf().',
        ],
        [
            'Calling toBeScalar() on Expectation<int|string>; assertion is redundant.',
            17,
            'The expectation value is already guaranteed to satisfy toBeScalar().',
        ],
        [
            'Calling toBeNumeric() on Expectation<string>; assertion is redundant.',
            24,
            'The expectation value is already guaranteed to satisfy toBeNumeric().',
        ],
    ]);
});

test('redundant semantic chains avoid duplicate diagnostics', function (): void {
    $this->analyse([
        __DIR__ . '/data/redundant-expectation-semantic-chain.php',
    ], [
        [
            'Calling toBeString() on Expectation<string>; assertion is redundant.',
            6,
            'The expectation value is already guaranteed to satisfy toBeString().',
        ],
    ]);
});
