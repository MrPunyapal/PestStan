<?php

declare(strict_types=1);

namespace Tests\Type;

use PestStan\Rules\StaticTestClosureRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<StaticTestClosureRule>
 */
class StaticTestClosureRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new StaticTestClosureRule;
    }

    /** @return string[] */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../extension.neon',
        ];
    }

    public function test_static_closures_are_reported(): void
    {
        $this->analyse([
            __DIR__ . '/data/static-test-closure.php',
        ], [
            [
                'Test closure passed to it() must not be static.',
                8,
            ],
            [
                'Test closure passed to test() must not be static.',
                12,
            ],
            [
                'Test closure passed to describe() must not be static.',
                16,
            ],
            [
                'Test closure passed to beforeEach() must not be static.',
                22,
            ],
            [
                'Test closure passed to afterEach() must not be static.',
                26,
            ],
            [
                'Test closure passed to beforeAll() must not be static.',
                30,
            ],
            [
                'Test closure passed to afterAll() must not be static.',
                34,
            ],
            [
                'Test closure passed to it() must not be static.',
                39,
            ],
        ]);
    }
}
