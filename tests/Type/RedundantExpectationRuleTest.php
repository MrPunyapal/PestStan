<?php

declare(strict_types=1);

namespace Tests\Type;

use PestStan\Rules\RedundantExpectationRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<RedundantExpectationRule>
 */
class RedundantExpectationRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new RedundantExpectationRule;
    }

    /** @return string[] */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../extension.neon',
        ];
    }

    public function test_redundant_expectations_are_reported(): void
    {
        $this->analyse([
            __DIR__ . '/data/redundant-expectation.php',
        ], [
            [
                'Calling toBeTrue() on Expectation<true> will always pass — the assertion is redundant.',
                7,
            ],
            [
                'Calling toBeFalse() on Expectation<false> will always pass — the assertion is redundant.',
                11,
            ],
            [
                'Calling toBeBool() on Expectation<true> will always pass — the assertion is redundant.',
                15,
            ],
            [
                'Calling toBeString() on Expectation<string> will always pass — the assertion is redundant.',
                19,
            ],
            [
                'Calling toBeInt() on Expectation<int> will always pass — the assertion is redundant.',
                23,
            ],
            [
                'Calling toBeFloat() on Expectation<float> will always pass — the assertion is redundant.',
                27,
            ],
            [
                'Calling toBeNull() on Expectation<null> will always pass — the assertion is redundant.',
                31,
            ],
            [
                'Calling toBeArray() on Expectation<array> will always pass — the assertion is redundant.',
                35,
            ],
            [
                'Calling toBeScalar() on Expectation<string> will always pass — the assertion is redundant.',
                39,
            ],
            [
                'Calling toBeNumeric() on Expectation<int> will always pass — the assertion is redundant.',
                43,
            ],
            [
                'Calling toBeInstanceOf() on Expectation<stdClass> will always pass — the assertion is redundant.',
                47,
            ],
        ]);
    }
}
