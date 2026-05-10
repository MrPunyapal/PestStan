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

test('config-style group names are validated', function (): void {
    $this->analyse([
        __DIR__ . '/data/invalid-group-name-config.php',
    ], [
        [
            'group() requires a non-empty string argument.',
            8,
        ],
        [
            'group() requires a non-empty string argument.',
            11,
        ],
        [
            'group() requires at least one non-empty string argument.',
            14,
        ],
        [
            'group() requires a non-empty string argument.',
            17,
        ],
        [
            'group() requires a non-empty string argument.',
            20,
        ],
        [
            'group() requires at least one non-empty string argument.',
            23,
        ],
    ]);
});
