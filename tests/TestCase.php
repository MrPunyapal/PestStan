<?php

declare(strict_types=1);

namespace Tests;

use PHPStan\Testing\TypeInferenceTestCase;

abstract class TestCase extends TypeInferenceTestCase
{
    /**
     * @var string[]
     */
    public static array $additionalConfigFiles = [];

    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return self::$additionalConfigFiles;
    }
}
