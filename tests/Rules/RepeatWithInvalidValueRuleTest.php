<?php

declare(strict_types=1);

namespace Tests\Rules;

use PestStan\Rules\RepeatWithInvalidValueRule;
use Tests\RuleTestCase;

beforeAll(function (): void {
    RuleTestCase::$rule = new RepeatWithInvalidValueRule;
    RuleTestCase::$additionalConfigFiles = [
        __DIR__ . '/../extension.neon',
    ];
});

test('repeat with invalid values is reported', function (): void {
    $this->analyse([
        __DIR__ . '/data/repeat-invalid-value.php',
    ], [
        [
            'repeat() requires a value greater than 0, got 0.',
            6,
        ],
        [
            'repeat() requires a value greater than 0, got -1.',
            11,
        ],
    ]);
});
