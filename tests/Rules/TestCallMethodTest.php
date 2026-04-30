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

test('testcall chain methods are allowed', function (): void {
    $this->analyse([
        __DIR__ . '/../Type/data/test-call-chain-methods.php',
    ], []);
});
