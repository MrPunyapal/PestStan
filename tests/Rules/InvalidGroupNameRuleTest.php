<?php

declare(strict_types=1);

namespace Tests\Rules;

use PestStan\Rules\InvalidGroupNameRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<InvalidGroupNameRule>
 */
class InvalidGroupNameRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new InvalidGroupNameRule;
    }

    /** @return string[] */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../extension.neon',
        ];
    }

    public function test_empty_group_name_is_reported(): void
    {
        $this->analyse([
            __DIR__ . '/data/invalid-group-name.php',
        ], [
            [
                'group() requires a non-empty string argument.',
                6,
            ],
            [
                'group() requires a non-empty string argument.',
                11,
            ],
        ]);
    }
}
