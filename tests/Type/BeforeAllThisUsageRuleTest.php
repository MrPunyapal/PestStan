<?php

declare(strict_types=1);

namespace Tests\Type;

use PestStan\Rules\BeforeAllThisUsageRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<BeforeAllThisUsageRule>
 */
class BeforeAllThisUsageRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new BeforeAllThisUsageRule;
    }

    /** @return string[] */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../extension.neon',
        ];
    }

    public function test_this_usage_in_before_all_is_reported(): void
    {
        $this->analyse([
            __DIR__ . '/data/before-all-this-usage.php',
        ], [
            [
                'beforeAll() runs in static context — $this is not available. Use beforeEach() instead.',
                7,
            ],
            [
                'beforeAll() runs in static context — $this is not available. Use beforeEach() instead.',
                11,
            ],
        ]);
    }
}
