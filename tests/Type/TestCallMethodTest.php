<?php

declare(strict_types=1);

namespace Tests\Type;

use PHPStan\Rules\Methods\CallMethodsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * Verifies that method.notFound, method.nonObject, and method.internalTrait
 * errors are suppressed for fluent method chains on TestCall
 * (e.g. arch()->preset()->php()).
 *
 * @extends RuleTestCase<CallMethodsRule>
 */
class TestCallMethodTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(CallMethodsRule::class);
    }

    /** @return string[] */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../extension.neon',
        ];
    }

    public function test_testcall_chain_methods_are_allowed(): void
    {
        $this->analyse([
            __DIR__ . '/data/test-call-chain-methods.php',
        ], []);
    }
}
