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
            'Calling each() on Expectation<int> — value is not iterable.',
            7,
            'Pass an iterable value to expect() before calling each().',
        ],
        [
            'Calling each() on Expectation<string> — value is not iterable.',
            11,
            'Pass an iterable value to expect() before calling each().',
        ],
        [
            'Calling sequence() on Expectation<int> — value is not iterable.',
            16,
            'Pass an iterable value to expect() before calling sequence().',
        ],
        [
            'Calling json() on Expectation<int> — value must be a string.',
            21,
            'Pass a string value to expect() before calling json().',
        ],
        [
            'Calling json() on Expectation<array<int, int>> — value must be a string.',
            25,
            'Pass a string value to expect() before calling json().',
        ],
        [
            'Calling toStartWith() on Expectation<int> — value must be a string.',
            30,
            'Pass a string value to expect() before calling toStartWith().',
        ],
        [
            'Calling toEndWith() on Expectation<int> — value must be a string.',
            34,
            'Pass a string value to expect() before calling toEndWith().',
        ],
        [
            'Calling toBeJson() on Expectation<int> — value must be a string.',
            38,
            'Pass a string value to expect() before calling toBeJson().',
        ],
        [
            'Calling toBeFile() on Expectation<int> — value must be a string.',
            42,
            'Pass a string value to expect() before calling toBeFile().',
        ],
        [
            'Calling toBeDirectory() on Expectation<int> — value must be a string.',
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
            'Calling toBeUppercase() on Expectation<int> — value must be a string.',
            7,
            'Pass a string value to expect() before calling toBeUppercase().',
        ],
        [
            'Calling toBeLowercase() on Expectation<int> — value must be a string.',
            11,
            'Pass a string value to expect() before calling toBeLowercase().',
        ],
        [
            'Calling toBeAlphaNumeric() on Expectation<int> — value must be a string.',
            15,
            'Pass a string value to expect() before calling toBeAlphaNumeric().',
        ],
        [
            'Calling toBeAlpha() on Expectation<int> — value must be a string.',
            19,
            'Pass a string value to expect() before calling toBeAlpha().',
        ],
        [
            'Calling toBeSnakeCase() on Expectation<int> — value must be a string.',
            23,
            'Pass a string value to expect() before calling toBeSnakeCase().',
        ],
        [
            'Calling toBeKebabCase() on Expectation<int> — value must be a string.',
            27,
            'Pass a string value to expect() before calling toBeKebabCase().',
        ],
        [
            'Calling toBeCamelCase() on Expectation<int> — value must be a string.',
            31,
            'Pass a string value to expect() before calling toBeCamelCase().',
        ],
        [
            'Calling toBeStudlyCase() on Expectation<int> — value must be a string.',
            35,
            'Pass a string value to expect() before calling toBeStudlyCase().',
        ],
        [
            'Calling toBeUuid() on Expectation<int> — value must be a string.',
            39,
            'Pass a string value to expect() before calling toBeUuid().',
        ],
        [
            'Calling toBeUrl() on Expectation<int> — value must be a string.',
            43,
            'Pass a string value to expect() before calling toBeUrl().',
        ],
        [
            'Calling toBeSlug() on Expectation<int> — value must be a string.',
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
            'Calling toContainEqual() on Expectation<int> — value is not iterable.',
            7,
            'Pass an iterable value to expect() before calling toContainEqual().',
        ],
        [
            'Calling toContainOnlyInstancesOf() on Expectation<string> — value is not iterable.',
            11,
            'Pass an iterable value to expect() before calling toContainOnlyInstancesOf().',
        ],
        [
            'Calling toBeDigits() on Expectation<int> — value must be a string.',
            16,
            'Pass a string value to expect() before calling toBeDigits().',
        ],
        [
            'Calling toMatch() on Expectation<int> — value must be a string.',
            20,
            'Pass a string value to expect() before calling toMatch().',
        ],
    ]);
});
