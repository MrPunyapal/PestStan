<?php

declare(strict_types=1);

namespace Tests\Rules;

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
                'Pass an iterable value to expect() before calling each().',
            ],
            [
                'Calling each() on Expectation<string> — value is not iterable.',
                11,
                'Pass an iterable value to expect() before calling each().',
            ],
            [
                'Calling sequence() on Expectation<int> — value is not iterable.',
                16,
                'Pass an iterable value to expect() before calling sequence().',
            ],
            [
                'Calling json() on Expectation<int> — value must be a string.',
                21,
                'Pass a string value to expect() before calling json().',
            ],
            [
                'Calling json() on Expectation<array<int, int>> — value must be a string.',
                25,
                'Pass a string value to expect() before calling json().',
            ],
            [
                'Calling toStartWith() on Expectation<int> — value must be a string.',
                30,
                'Pass a string value to expect() before calling toStartWith().',
            ],
            [
                'Calling toEndWith() on Expectation<int> — value must be a string.',
                34,
                'Pass a string value to expect() before calling toEndWith().',
            ],
            [
                'Calling toBeJson() on Expectation<int> — value must be a string.',
                38,
                'Pass a string value to expect() before calling toBeJson().',
            ],
            [
                'Calling toBeFile() on Expectation<int> — value must be a string.',
                42,
                'Pass a string value to expect() before calling toBeFile().',
            ],
            [
                'Calling toBeDirectory() on Expectation<int> — value must be a string.',
                46,
                'Pass a string value to expect() before calling toBeDirectory().',
            ],
        ]);
    }
}
