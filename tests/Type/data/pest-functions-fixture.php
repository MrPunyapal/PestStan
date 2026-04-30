<?php

declare(strict_types=1);

namespace PestFunctions;

use function PHPStan\Testing\assertType;

function testFixtureReturnType(): void
{
    assertType('string', fixture('example.json'));
}
