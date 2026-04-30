<?php

declare(strict_types=1);

namespace Tests\Rules;

use PestStan\Rules\DescribeWithoutTestsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DescribeWithoutTestsRule>
 */
class DescribeWithoutTestsRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new DescribeWithoutTestsRule;
    }

    /** @return string[] */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../extension.neon',
        ];
    }

    public function test_describe_without_tests_is_reported(): void
    {
        $this->analyse([
            __DIR__ . '/data/describe-without-tests.php',
        ], [
            [
                "describe() block 'empty group' contains no tests.",
                6,
            ],
            [
                "describe() block 'hooks only' contains no tests.",
                10,
            ],
            [
                "describe() block 'hooks with chain only' contains no tests.",
                17,
            ],
        ]);
    }
}
