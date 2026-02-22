<?php

declare(strict_types=1);

namespace TestCallMethods;

use Pest\PendingCalls\TestCall;

use function PHPStan\Testing\assertType;

use RuntimeException;

function testItReturnsTestCall(): void
{
    assertType(TestCall::class, it('does something', function (): void {}));
}

function testTestReturnsTestCall(): void
{
    assertType(TestCall::class, test('does something', function (): void {}));
}

function testWithChaining(): void
{
    $result = it('does something', function (): void {})->with(['a', 'b']);
    assertType(TestCall::class, $result);
}

function testGroupChaining(): void
{
    $result = it('does something', function (): void {})->group('unit');
    assertType(TestCall::class, $result);
}

function testSkipChaining(): void
{
    $result = it('does something', function (): void {})->skip();
    assertType(TestCall::class, $result);
}

function testOnlyChaining(): void
{
    $result = it('does something', function (): void {})->only();
    assertType(TestCall::class, $result);
}

function testTodoChaining(): void
{
    $result = it('does something', function (): void {})->todo();
    assertType(TestCall::class, $result);
}

function testDependsChaining(): void
{
    $result = it('does something', function (): void {})->depends('other test');
    assertType(TestCall::class, $result);
}

function testThrowsChaining(): void
{
    $result = it('does something', function (): void {})->throws(RuntimeException::class);
    assertType(TestCall::class, $result);
}

function testMultipleChaining(): void
{
    $result = it('does something', function (): void {})
        ->with(['a', 'b'])
        ->group('unit', 'feature')
        ->skip(false)
        ->depends('other test');
    assertType(TestCall::class, $result);
}

function testRepeatChaining(): void
{
    $result = it('does something', function (): void {})->repeat(3);
    assertType(TestCall::class, $result);
}

function testThrowsNoExceptionsChaining(): void
{
    $result = it('does something', function (): void {})->throwsNoExceptions();
    assertType(TestCall::class, $result);
}

function testCoversChaining(): void
{
    $result = it('does something', function (): void {})->covers('App\MyClass');
    assertType(TestCall::class, $result);
}

function testPlatformSkipMethods(): void
{
    assertType(TestCall::class, it('test', function (): void {})->skipOnWindows());
    assertType(TestCall::class, it('test', function (): void {})->skipOnMac());
    assertType(TestCall::class, it('test', function (): void {})->skipOnLinux());
    assertType(TestCall::class, it('test', function (): void {})->onlyOnWindows());
    assertType(TestCall::class, it('test', function (): void {})->onlyOnMac());
    assertType(TestCall::class, it('test', function (): void {})->onlyOnLinux());
}
