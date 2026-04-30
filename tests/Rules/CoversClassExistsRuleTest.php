<?php

declare(strict_types=1);

namespace Tests\Rules;

use PestStan\Rules\CoversClassExistsRule;
use Tests\RuleTestCase;

beforeAll(function (): void {
    RuleTestCase::$rule = RuleTestCase::resolveRule(CoversClassExistsRule::class);
    RuleTestCase::$additionalConfigFiles = [
        __DIR__ . '/../extension.neon',
    ];
});

test('non existent class in covers class is reported', function (): void {
    $this->analyse([
        __DIR__ . '/data/covers-class-exists.php',
    ], [
        [
            'Class App\NonExistent\FakeClass referenced in coversClass() does not exist.',
            8,
        ],
        [
            'Class App\NonExistent\SecondFakeClass referenced in coversClass() does not exist.',
            18,
        ],
        [
            'Function missing_test_helper() referenced in coversFunction() does not exist.',
            23,
        ],
    ]);
});
