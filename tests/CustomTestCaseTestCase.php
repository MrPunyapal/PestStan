<?php

declare(strict_types=1);

namespace Tests;

use PHPStan\Testing\TypeInferenceTestCase;

abstract class CustomTestCaseTestCase extends TypeInferenceTestCase
{
    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/Type/custom-testcase-extension.neon',
        ];
    }
}
