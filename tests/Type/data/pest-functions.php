<?php

declare(strict_types=1);

namespace PestFunctions;

use function PHPStan\Testing\assertType;

function testUsesReturnType(): void
{
    assertType('Pest\PendingCalls\UsesCall', uses(\PHPUnit\Framework\TestCase::class));
}

function testPestReturnType(): void
{
    assertType('Pest\Configuration', pest());
}

function testDescribeReturnType(): void
{
    assertType('Pest\PendingCalls\DescribeCall', describe('group', function (): void {}));
}

function testTodoFunctionReturnType(): void
{
    assertType('Pest\PendingCalls\TestCall', todo('implement later'));
}

function testBeforeEachReturnType(): void
{
    assertType('Pest\PendingCalls\BeforeEachCall', beforeEach(function (): void {}));
}

function testAfterEachReturnType(): void
{
    assertType('Pest\PendingCalls\AfterEachCall', afterEach(function (): void {}));
}
