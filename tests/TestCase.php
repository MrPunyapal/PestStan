<?php

declare(strict_types=1);

namespace Tests;

use PHPStan\Testing\TypeInferenceTestCase;

abstract class TestCase extends TypeInferenceTestCase
{
    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/extension.neon',
        ];
    }
}
