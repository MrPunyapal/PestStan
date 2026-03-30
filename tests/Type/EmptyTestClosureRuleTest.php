<?php

declare(strict_types=1);

namespace Tests\Type;

use PestStan\Rules\EmptyTestClosureRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<EmptyTestClosureRule>
 */
class EmptyTestClosureRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new EmptyTestClosureRule;
    }

    /** @return string[] */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../extension.neon',
        ];
    }

    public function test_empty_closures_are_reported(): void
    {
        $this->analyse([
            __DIR__ . '/data/empty-test-closure.php',
        ], [
            [
                "Test 'empty it closure' has an empty closure body. Did you forget to add assertions?",
                5,
            ],
            [
                "Test 'empty test closure' has an empty closure body. Did you forget to add assertions?",
                8,
            ],
        ]);
    }
}
