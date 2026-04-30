<?php

declare(strict_types=1);

namespace Tests\Rules;

use PestStan\Rules\InvalidGroupNameRule;
use Tests\RuleTestCase;

beforeAll(function (): void {
    RuleTestCase::$rule = new InvalidGroupNameRule;
    RuleTestCase::$additionalConfigFiles = [
        __DIR__ . '/../extension.neon',
    ];
});

test('empty group name is reported', function (): void {
    $this->analyse([
        __DIR__ . '/data/invalid-group-name.php',
    ], [
        [
            'group() requires a non-empty string argument.',
            6,
        ],
        [
            'group() requires a non-empty string argument.',
            11,
        ],
    ]);
});
