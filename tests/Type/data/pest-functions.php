<?php

declare(strict_types=1);

namespace PestFunctions;

use Pest\Configuration;
use Pest\PendingCalls\AfterEachCall;
use Pest\PendingCalls\BeforeEachCall;
use Pest\PendingCalls\DescribeCall;
use Pest\PendingCalls\TestCall;
use Pest\PendingCalls\UsesCall;

use function PHPStan\Testing\assertType;

use PHPUnit\Framework\TestCase;

function testUsesReturnType(): void
{
    assertType(UsesCall::class, uses(TestCase::class));
    assertType(UsesCall::class, pest()->extend(TestCase::class));
}

function testPestReturnType(): void
{
    assertType(Configuration::class, pest());
}

function testDescribeReturnType(): void
{
    assertType(DescribeCall::class, describe('group', function (): void {}));
}

function testTodoFunctionReturnType(): void
{
    assertType(TestCall::class, todo('implement later'));
}

function testBeforeEachReturnType(): void
{
    assertType(BeforeEachCall::class, beforeEach(function (): void {}));
}

function testAfterEachReturnType(): void
{
    assertType(AfterEachCall::class, afterEach(function (): void {}));
}

function testBeforeAllReturnType(): void
{
    assertType('null', beforeAll(function (): void {}));
}

function testAfterAllReturnType(): void
{
    assertType('null', afterAll(function (): void {}));
}

function testDatasetReturnType(): void
{
    assertType('null', dataset('numbers', [1, 2, 3]));
}

function testCoversReturnType(): void
{
    assertType('null', covers(TestCase::class));
}

function testMutatesReturnType(): void
{
    assertType('null', mutates(TestCase::class));
}
