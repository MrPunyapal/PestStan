<?php

declare(strict_types=1);

use PHPStan\Testing\TypeInferenceTestCase;

beforeAll(function (): void {
    self::$additionalConfigFiles = [__DIR__ . '/../extension.neon'];
});

test('type assertions', function (string $assertType, string $file, mixed ...$args): void {
    $this->assertFileAsserts($assertType, $file, ...$args);
})->with(function (): Iterator {
    yield from TypeInferenceTestCase::gatherAssertTypes(__DIR__ . '/data/expect-function.php');
    yield from TypeInferenceTestCase::gatherAssertTypes(__DIR__ . '/data/test-closures.php');
    yield from TypeInferenceTestCase::gatherAssertTypes(__DIR__ . '/data/expectation-methods.php');
});

