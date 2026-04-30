<?php

declare(strict_types=1);

namespace Tests\Rules;

use PHPStan\Rules\Methods\CallMethodsRule;
use Tests\RuleTestCase;

beforeAll(function (): void {
    RuleTestCase::$rule = RuleTestCase::resolveRule(CallMethodsRule::class);
    RuleTestCase::$additionalConfigFiles = [
        __DIR__ . '/../Type/custom-testcase-extension.neon',
    ];
});

test('protected method calls are allowed with custom test case', function (): void {
    $this->analyse([
        __DIR__ . '/data/protected-method-calls-custom-testcase.php',
    ], []);
});
