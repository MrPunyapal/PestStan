<?php

declare(strict_types=1);

namespace Tests\Rules;

use PestStan\Rules\DisallowedCallInDescribeRule;
use Tests\RuleTestCase;

beforeAll(function (): void {
    RuleTestCase::$rule = new DisallowedCallInDescribeRule;
    RuleTestCase::$additionalConfigFiles = [
        __DIR__ . '/../extension.neon',
    ];
});

test('before all in describe is reported', function (): void {
    $this->analyse([
        __DIR__ . '/data/disallowed-call-in-describe.php',
    ], [
        [
            'beforeAll() cannot be used inside describe() blocks. Use beforeEach() instead.',
            6,
        ],
        [
            'afterAll() cannot be used inside describe() blocks. Use afterEach() instead.',
            16,
        ],
        [
            'beforeAll() cannot be used inside describe() blocks. Use beforeEach() instead.',
            26,
        ],
        [
            'afterAll() cannot be used inside describe() blocks. Use afterEach() instead.',
            29,
        ],
    ]);
});
