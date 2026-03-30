<?php

declare(strict_types=1);

namespace Tests\Type;

use PestStan\Rules\ExpectationValueTypeRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ExpectationValueTypeRule>
 */
class ExpectationValueTypeRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ExpectationValueTypeRule;
    }

    /** @return string[] */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../extension.neon',
        ];
    }

    public function test_expectation_value_type_mismatches_are_reported(): void
    {
        $this->analyse([
            __DIR__ . '/data/expectation-value-type.php',
        ], [
            [
                'Calling each() on Expectation<int> — value is not iterable.',
                7,
            ],
            [
                'Calling each() on Expectation<string> — value is not iterable.',
                11,
            ],
            [
                'Calling sequence() on Expectation<int> — value is not iterable.',
                16,
            ],
            [
                'Calling json() on Expectation<int> — value must be a string.',
                21,
            ],
            [
                'Calling json() on Expectation<array<int, int>> — value must be a string.',
                25,
            ],
            [
                'Calling toStartWith() on Expectation<int> — value must be a string.',
                30,
            ],
            [
                'Calling toEndWith() on Expectation<int> — value must be a string.',
                34,
            ],
            [
                'Calling toBeJson() on Expectation<int> — value must be a string.',
                38,
            ],
            [
                'Calling toBeFile() on Expectation<int> — value must be a string.',
                42,
            ],
            [
                'Calling toBeDirectory() on Expectation<int> — value must be a string.',
                46,
            ],
        ]);
    }
}
