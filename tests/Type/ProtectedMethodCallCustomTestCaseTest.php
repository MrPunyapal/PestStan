<?php

declare(strict_types=1);

namespace Tests\Type;

use PHPStan\Rules\Methods\CallMethodsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * Verifies that method.protected errors are suppressed when the configured
 * testCaseClass is a custom class extending TestCase.
 *
 * @extends RuleTestCase<CallMethodsRule>
 */
class ProtectedMethodCallCustomTestCaseTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(CallMethodsRule::class);
    }

    /** @return string[] */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/custom-testcase-extension.neon',
        ];
    }

    public function test_protected_method_calls_are_allowed_with_custom_test_case(): void
    {
        $this->analyse([
            __DIR__ . '/data/protected-method-calls-custom-testcase.php',
        ], []);
    }
}
