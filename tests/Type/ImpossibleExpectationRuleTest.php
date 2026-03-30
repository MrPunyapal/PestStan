<?php

declare(strict_types=1);

namespace Tests\Type;

use PestStan\Rules\ImpossibleExpectationRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ImpossibleExpectationRule>
 */
class ImpossibleExpectationRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ImpossibleExpectationRule;
    }

    /** @return string[] */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../extension.neon',
        ];
    }

    public function test_impossible_expectations_are_reported(): void
    {
        $this->analyse([
            __DIR__ . '/data/impossible-expectation.php',
        ], [
            [
                'Calling toBeString() on Expectation<int> will always fail.',
                7,
            ],
            [
                'Calling toBeInt() on Expectation<string> will always fail.',
                11,
            ],
            [
                'Calling toBeFloat() on Expectation<string> will always fail.',
                15,
            ],
            [
                'Calling toBeBool() on Expectation<string> will always fail.',
                19,
            ],
            [
                'Calling toBeTrue() on Expectation<int> will always fail.',
                23,
            ],
            [
                'Calling toBeFalse() on Expectation<int> will always fail.',
                27,
            ],
            [
                'Calling toBeNull() on Expectation<string> will always fail.',
                31,
            ],
            [
                'Calling toBeArray() on Expectation<string> will always fail.',
                35,
            ],
            [
                'Calling toBeObject() on Expectation<int> will always fail.',
                39,
            ],
            [
                'Calling toBeIterable() on Expectation<int> will always fail.',
                43,
            ],
            [
                'Calling toBeCallable() on Expectation<null> will always fail.',
                47,
            ],
            [
                'Calling toBeInstanceOf() on Expectation<int> will always fail.',
                51,
            ],
            [
                'Calling toBeScalar() on Expectation<array> will always fail.',
                55,
            ],
            [
                'Calling toBeNumeric() on Expectation<null> will always fail.',
                59,
            ],
            [
                'Calling toBeInt() on Expectation<string> will always fail.',
                74,
            ],
            [
                'Calling toBeInt() on Expectation<string> will always fail.',
                80,
            ],
        ]);
    }
}
