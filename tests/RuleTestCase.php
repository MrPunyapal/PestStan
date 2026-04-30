<?php

declare(strict_types=1);

namespace Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase as RuleTestCaseCore;

abstract class RuleTestCase extends RuleTestCaseCore
{
    public static Rule $rule;

    /**
     * @var string[]
     */
    public static array $additionalConfigFiles = [];

    protected function getRule(): Rule
    {
        return self::$rule;
    }

    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return self::$additionalConfigFiles;
    }

    /**
     * @param  class-string<Rule>  $ruleClass
     */
    public static function resolveRule(string $ruleClass): Rule
    {
        return self::getContainer()->getByType($ruleClass);
    }
}
