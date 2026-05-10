<?php

declare(strict_types=1);

namespace Tests\Rules;

use PestStan\Rules\ExpectationValueTypeRule;
use Tests\RuleTestCase;

beforeAll(function (): void {
    RuleTestCase::$rule = new ExpectationValueTypeRule;
    RuleTestCase::$additionalConfigFiles = [
        __DIR__ . '/../extension.neon',
    ];
});

test('expectation value type mismatches are reported', function (): void {
    $this->analyse([
        __DIR__ . '/data/expectation-value-type.php',
    ], [
        [
            'Calling each() on Expectation<int>; matcher requires iterable.',
            7,
            'Pass an iterable value to expect() before calling each().',
        ],
        [
            'Calling each() on Expectation<string>; matcher requires iterable.',
            11,
            'Pass an iterable value to expect() before calling each().',
        ],
        [
            'Calling sequence() on Expectation<int>; matcher requires iterable.',
            16,
            'Pass an iterable value to expect() before calling sequence().',
        ],
        [
            'Calling json() on Expectation<int>; matcher requires string.',
            21,
            'Pass a string value to expect() before calling json().',
        ],
        [
            'Calling json() on Expectation<array<int, int>>; matcher requires string.',
            25,
            'Pass a string value to expect() before calling json().',
        ],
        [
            'Calling toStartWith() on Expectation<int>; matcher requires string.',
            30,
            'Pass a string value to expect() before calling toStartWith().',
        ],
        [
            'Calling toEndWith() on Expectation<int>; matcher requires string.',
            34,
            'Pass a string value to expect() before calling toEndWith().',
        ],
        [
            'Calling toBeJson() on Expectation<int>; matcher requires string.',
            38,
            'Pass a string value to expect() before calling toBeJson().',
        ],
        [
            'Calling toBeFile() on Expectation<int>; matcher requires string.',
            42,
            'Pass a string value to expect() before calling toBeFile().',
        ],
        [
            'Calling toBeDirectory() on Expectation<int>; matcher requires string.',
            46,
            'Pass a string value to expect() before calling toBeDirectory().',
        ],
    ]);
});

test('rector-pest string matchers require string expectation values', function (): void {
    $this->analyse([
        __DIR__ . '/data/expectation-modern-string-matchers.php',
    ], [
        [
            'Calling toBeUppercase() on Expectation<int>; matcher requires string.',
            7,
            'Pass a string value to expect() before calling toBeUppercase().',
        ],
        [
            'Calling toBeLowercase() on Expectation<int>; matcher requires string.',
            11,
            'Pass a string value to expect() before calling toBeLowercase().',
        ],
        [
            'Calling toBeAlphaNumeric() on Expectation<int>; matcher requires string.',
            15,
            'Pass a string value to expect() before calling toBeAlphaNumeric().',
        ],
        [
            'Calling toBeAlpha() on Expectation<int>; matcher requires string.',
            19,
            'Pass a string value to expect() before calling toBeAlpha().',
        ],
        [
            'Calling toBeSnakeCase() on Expectation<int>; matcher requires string.',
            23,
            'Pass a string value to expect() before calling toBeSnakeCase().',
        ],
        [
            'Calling toBeKebabCase() on Expectation<int>; matcher requires string.',
            27,
            'Pass a string value to expect() before calling toBeKebabCase().',
        ],
        [
            'Calling toBeCamelCase() on Expectation<int>; matcher requires string.',
            31,
            'Pass a string value to expect() before calling toBeCamelCase().',
        ],
        [
            'Calling toBeStudlyCase() on Expectation<int>; matcher requires string.',
            35,
            'Pass a string value to expect() before calling toBeStudlyCase().',
        ],
        [
            'Calling toBeUuid() on Expectation<int>; matcher requires string.',
            39,
            'Pass a string value to expect() before calling toBeUuid().',
        ],
        [
            'Calling toBeUrl() on Expectation<int>; matcher requires string.',
            43,
            'Pass a string value to expect() before calling toBeUrl().',
        ],
        [
            'Calling toBeSlug() on Expectation<int>; matcher requires string.',
            47,
            'Pass a string value to expect() before calling toBeSlug().',
        ],
    ]);
});

test('additional expectation matchers enforce proven value types', function (): void {
    $this->analyse([
        __DIR__ . '/data/expectation-additional-value-type.php',
    ], [
        [
            'Calling toContainEqual() on Expectation<int>; matcher requires iterable.',
            7,
            'Pass an iterable value to expect() before calling toContainEqual().',
        ],
        [
            'Calling toContainOnlyInstancesOf() on Expectation<string>; matcher requires iterable.',
            11,
            'Pass an iterable value to expect() before calling toContainOnlyInstancesOf().',
        ],
        [
            'Calling toBeDigits() on Expectation<int>; matcher requires string.',
            16,
            'Pass a string value to expect() before calling toBeDigits().',
        ],
        [
            'Calling toMatch() on Expectation<int>; matcher requires string.',
            20,
            'Pass a string value to expect() before calling toMatch().',
        ],
    ]);
});

test('countable and chain-aware matcher requirements are reported', function (): void {
    $this->analyse([
        __DIR__ . '/data/expectation-countable-analysis.php',
    ], [
        [
            'Calling toHaveCount() on Expectation<string>; matcher requires countable or iterable.',
            9,
            'Pass a countable or iterable value to expect() before calling toHaveCount().',
        ],
        [
            'Calling toHaveSameSize() on Expectation<int>; matcher requires countable or iterable.',
            13,
            'Pass a countable or iterable value to expect() before calling toHaveSameSize().',
        ],
        [
            'Calling each() on Expectation<int>; matcher requires iterable.',
            17,
            'Pass an iterable value to expect() before calling each().',
        ],
        [
            'Calling sequence() on Expectation<int>; matcher requires iterable.',
            21,
            'Pass an iterable value to expect() before calling sequence().',
        ],
        [
            'Calling toHaveCount() on Expectation<string>; matcher requires countable or iterable.',
            29,
            'Pass a countable or iterable value to expect() before calling toHaveCount().',
        ],
    ]);
});
