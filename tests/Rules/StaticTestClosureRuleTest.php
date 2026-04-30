<?php

declare(strict_types=1);

namespace Tests\Rules;

use PestStan\Rules\StaticTestClosureRule;
use Tests\RuleTestCase;

beforeAll(function (): void {
    RuleTestCase::$rule = new StaticTestClosureRule;
    RuleTestCase::$additionalConfigFiles = [
        __DIR__ . '/../extension.neon',
    ];
});

test('static closures are reported', function (): void {
    $this->analyse([
        __DIR__ . '/data/static-test-closure.php',
    ], [
        [
            'Test closure passed to it() must not be static. Remove the `static` keyword.',
            8,
        ],
        [
            'Test closure passed to test() must not be static. Remove the `static` keyword.',
            12,
        ],
        [
            'Test closure passed to describe() must not be static. Remove the `static` keyword.',
            16,
        ],
        [
            'Test closure passed to beforeEach() must not be static. Remove the `static` keyword.',
            22,
        ],
        [
            'Test closure passed to afterEach() must not be static. Remove the `static` keyword.',
            26,
        ],
        [
            'Test closure passed to beforeAll() must not be static. Remove the `static` keyword.',
            30,
        ],
        [
            'Test closure passed to afterAll() must not be static. Remove the `static` keyword.',
            34,
        ],
        [
            'Test closure passed to it() must not be static. Remove the `static` keyword.',
            39,
        ],
    ]);
});
