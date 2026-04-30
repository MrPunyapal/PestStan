<?php

declare(strict_types=1);

namespace Tests\Rules;

use PHPStan\Rules\Methods\CallMethodsRule;
use Tests\RuleTestCase;

beforeAll(function (): void {
    RuleTestCase::$rule = RuleTestCase::resolveRule(CallMethodsRule::class);
    RuleTestCase::$additionalConfigFiles = [
        __DIR__ . '/../extension.neon',
    ];
});

test('protected method calls are allowed in pest closures', function (): void {
    $this->analyse([
        __DIR__ . '/data/protected-method-calls.php',
    ], []);
});
