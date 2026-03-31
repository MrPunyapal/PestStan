<?php

declare(strict_types=1);

namespace Tests\Type;

use PestStan\Rules\MissingAssertionRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<MissingAssertionRule>
 */
class MissingAssertionRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new MissingAssertionRule;
    }

    /** @return string[] */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../extension.neon',
        ];
    }

    public function test_tests_without_assertions_are_reported(): void
    {
        $this->analyse([
            __DIR__ . '/data/missing-assertion.php',
        ], [
            [
                "Test 'does calculation' has no assertions. Did you forget expect()?",
                6,
            ],
            [
                "Test 'does setup only' has no assertions. Did you forget expect()?",
                10,
            ],
        ]);
    }
}
