<?php

declare(strict_types=1);

namespace Tests\Type;

use PestStan\Rules\RepeatWithInvalidValueRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<RepeatWithInvalidValueRule>
 */
class RepeatWithInvalidValueRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new RepeatWithInvalidValueRule;
    }

    /** @return string[] */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../extension.neon',
        ];
    }

    public function test_repeat_with_invalid_values_is_reported(): void
    {
        $this->analyse([
            __DIR__ . '/data/repeat-invalid-value.php',
        ], [
            [
                'repeat() requires a value greater than 0, got 0.',
                6,
                'This can be auto-fixed with rector-pest.',
            ],
            [
                'repeat() requires a value greater than 0, got -1.',
                11,
                'This can be auto-fixed with rector-pest.',
            ],
        ]);
    }
}
