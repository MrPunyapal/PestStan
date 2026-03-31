<?php

declare(strict_types=1);

namespace Tests\Type;

use PestStan\Rules\CoversClassExistsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<CoversClassExistsRule>
 */
class CoversClassExistsRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(CoversClassExistsRule::class);
    }

    /** @return string[] */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../extension.neon',
        ];
    }

    public function test_non_existent_class_in_covers_class_is_reported(): void
    {
        $this->analyse([
            __DIR__ . '/data/covers-class-exists.php',
        ], [
            [
                'Class App\NonExistent\FakeClass referenced in coversClass() does not exist.',
                8,
            ],
        ]);
    }
}
