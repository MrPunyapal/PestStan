<?php

declare(strict_types=1);

namespace Tests\Rules;

use PestStan\Rules\BeforeAllThisUsageRule;
use Tests\RuleTestCase;

beforeAll(function (): void {
    RuleTestCase::$rule = new BeforeAllThisUsageRule;
    RuleTestCase::$additionalConfigFiles = [
        __DIR__ . '/../extension.neon',
    ];
});

test('this usage in before all is reported', function (): void {
    $this->analyse([
        __DIR__ . '/data/before-all-this-usage.php',
    ], [
        [
            'beforeAll() runs in static context — $this is not available. Use beforeEach() instead.',
            7,
        ],
        [
            'beforeAll() runs in static context — $this is not available. Use beforeEach() instead.',
            11,
        ],
    ]);
});
