<?php

declare(strict_types=1);

namespace Tests\Type;

use PestStan\Rules\InvalidThrowsExceptionRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<InvalidThrowsExceptionRule>
 */
class InvalidThrowsExceptionRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(InvalidThrowsExceptionRule::class);
    }

    /** @return string[] */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../extension.neon',
        ];
    }

    public function test_non_throwable_class_in_throws_is_reported(): void
    {
        $this->analyse([
            __DIR__ . '/data/invalid-throws-exception.php',
        ], [
            [
                'throws() expects a Throwable class, got stdClass.',
                6,
            ],
        ]);
    }
}
