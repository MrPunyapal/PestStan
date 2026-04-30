<?php

declare(strict_types=1);

namespace Tests\Rules;

use PestStan\Rules\InvalidThrowsExceptionRule;
use Tests\RuleTestCase;

beforeAll(function (): void {
    RuleTestCase::$rule = RuleTestCase::resolveRule(InvalidThrowsExceptionRule::class);
    RuleTestCase::$additionalConfigFiles = [
        __DIR__ . '/../extension.neon',
    ];
});

test('non throwable class in throws is reported', function (): void {
    $this->analyse([
        __DIR__ . '/data/invalid-throws-exception.php',
    ], [
        [
            'throws() expects a Throwable class, got stdClass.',
            6,
        ],
    ]);
});
