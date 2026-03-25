<?php

declare(strict_types=1);

namespace Tests\Type;

use PHPStan\Rules\Methods\CallMethodsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * Verifies that method.protected errors are suppressed for $this->method() calls
 * inside Pest test closures, where $this is typed as the configured TestCase class.
 *
 * @extends RuleTestCase<CallMethodsRule>
 */
class ProtectedMethodCallTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(CallMethodsRule::class);
    }

    /** @return string[] */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../extension.neon',
        ];
    }

    public function test_protected_method_calls_are_allowed_in_pest_closures(): void
    {
        $this->analyse([
            __DIR__ . '/data/protected-method-calls.php',
        ], []);
    }
}
