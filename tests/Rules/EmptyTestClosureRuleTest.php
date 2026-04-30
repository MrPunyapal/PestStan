<?php

declare(strict_types=1);

namespace Tests\Rules;

use PestStan\Rules\EmptyTestClosureRule;
use Tests\RuleTestCase;

beforeAll(function (): void {
    RuleTestCase::$rule = new EmptyTestClosureRule;
    RuleTestCase::$additionalConfigFiles = [
        __DIR__ . '/../extension.neon',
    ];
});

test('empty closures are reported', function (): void {
    $this->analyse([
        __DIR__ . '/data/empty-test-closure.php',
    ], [
        [
            "Test 'empty it closure' has an empty closure body. Add assertions or chain ->todo() to mark as pending.",
            5,
        ],
        [
            "Test 'empty test closure' has an empty closure body. Add assertions or chain ->todo() to mark as pending.",
            7,
        ],
    ]);
});
