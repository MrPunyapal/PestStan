<?php

declare(strict_types=1);

namespace Tests\Rules;

use PestStan\Rules\DuplicateTestDescriptionRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DuplicateTestDescriptionRule>
 */
class DuplicateTestDescriptionRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new DuplicateTestDescriptionRule;
    }

    /** @return string[] */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../extension.neon',
        ];
    }

    public function test_duplicate_descriptions_are_reported(): void
    {
        $this->analyse([
            __DIR__ . '/data/duplicate-test-description.php',
        ], [
            [
                "A test with the description 'it does something' already exists in this file.",
                10,
            ],
            [
                "A test with the description 'another test' already exists in this file.",
                19,
            ],
            [
                "A test with the description 'it matches cross-function' already exists in this file.",
                28,
            ],
        ]);
    }
}
