<?php

declare(strict_types=1);

namespace Tests\Rules;

use PestStan\Rules\DisallowedCallInDescribeRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DisallowedCallInDescribeRule>
 */
class DisallowedCallInDescribeRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new DisallowedCallInDescribeRule;
    }

    /** @return string[] */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../extension.neon',
        ];
    }

    public function test_before_all_in_describe_is_reported(): void
    {
        $this->analyse([
            __DIR__ . '/data/disallowed-call-in-describe.php',
        ], [
            [
                'beforeAll() cannot be used inside describe() blocks. Use beforeEach() instead.',
                6,
            ],
            [
                'afterAll() cannot be used inside describe() blocks. Use afterEach() instead.',
                16,
            ],
            [
                'beforeAll() cannot be used inside describe() blocks. Use beforeEach() instead.',
                26,
            ],
            [
                'afterAll() cannot be used inside describe() blocks. Use afterEach() instead.',
                29,
            ],
        ]);
    }
}
