<?php

declare(strict_types=1);

namespace Tests\Rules;

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
                'Consider removing this assertion — the value is already guaranteed to be this type.',
            ],
            [
                'Calling toBeFalse() on Expectation<false> will always pass — the assertion is redundant.',
                11,
                'Consider removing this assertion — the value is already guaranteed to be this type.',
            ],
            [
                'Calling toBeBool() on Expectation<true> will always pass — the assertion is redundant.',
                15,
                'Consider removing this assertion — the value is already guaranteed to be this type.',
            ],
            [
                'Calling toBeString() on Expectation<string> will always pass — the assertion is redundant.',
                19,
                'Consider removing this assertion — the value is already guaranteed to be this type.',
            ],
            [
                'Calling toBeInt() on Expectation<int> will always pass — the assertion is redundant.',
                23,
                'Consider removing this assertion — the value is already guaranteed to be this type.',
            ],
            [
                'Calling toBeFloat() on Expectation<float> will always pass — the assertion is redundant.',
                27,
                'Consider removing this assertion — the value is already guaranteed to be this type.',
            ],
            [
                'Calling toBeNull() on Expectation<null> will always pass — the assertion is redundant.',
                31,
                'Consider removing this assertion — the value is already guaranteed to be this type.',
            ],
            [
                'Calling toBeArray() on Expectation<array> will always pass — the assertion is redundant.',
                35,
                'Consider removing this assertion — the value is already guaranteed to be this type.',
            ],
            [
                'Calling toBeScalar() on Expectation<string> will always pass — the assertion is redundant.',
                39,
                'Consider removing this assertion — the value is already guaranteed to be this type.',
            ],
            [
                'Calling toBeNumeric() on Expectation<int> will always pass — the assertion is redundant.',
                43,
                'Consider removing this assertion — the value is already guaranteed to be this type.',
            ],
            [
                'Calling toBeInstanceOf() on Expectation<stdClass> will always pass — the assertion is redundant.',
                47,
                'Consider removing this assertion — the value is already guaranteed to be this type.',
            ],
        ]);
    }
}
