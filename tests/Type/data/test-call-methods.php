<?php

declare(strict_types=1);

namespace TestCallMethods;

use function PHPStan\Testing\assertType;

function testItReturnsTestCall(): void
{
    assertType('Pest\PendingCalls\TestCall', it('does something', function (): void {}));
}

function testTestReturnsTestCall(): void
{
    assertType('Pest\PendingCalls\TestCall', test('does something', function (): void {}));
}

function testWithChaining(): void
{
    $result = it('does something', function (): void {})->with(['a', 'b']);
    assertType('Pest\PendingCalls\TestCall', $result);
}

function testGroupChaining(): void
{
    $result = it('does something', function (): void {})->group('unit');
    assertType('Pest\PendingCalls\TestCall', $result);
}

function testSkipChaining(): void
{
    $result = it('does something', function (): void {})->skip();
    assertType('Pest\PendingCalls\TestCall', $result);
}

function testOnlyChaining(): void
{
    $result = it('does something', function (): void {})->only();
    assertType('Pest\PendingCalls\TestCall', $result);
}

function testTodoChaining(): void
{
    $result = it('does something', function (): void {})->todo();
    assertType('Pest\PendingCalls\TestCall', $result);
}

function testDependsChaining(): void
{
    $result = it('does something', function (): void {})->depends('other test');
    assertType('Pest\PendingCalls\TestCall', $result);
}

function testThrowsChaining(): void
{
    $result = it('does something', function (): void {})->throws(\RuntimeException::class);
    assertType('Pest\PendingCalls\TestCall', $result);
}

function testMultipleChaining(): void
{
    $result = it('does something', function (): void {})
        ->with(['a', 'b'])
        ->group('unit', 'feature')
        ->skip(false)
        ->depends('other test');
    assertType('Pest\PendingCalls\TestCall', $result);
}

function testRepeatChaining(): void
{
    $result = it('does something', function (): void {})->repeat(3);
    assertType('Pest\PendingCalls\TestCall', $result);
}

function testThrowsNoExceptionsChaining(): void
{
    $result = it('does something', function (): void {})->throwsNoExceptions();
    assertType('Pest\PendingCalls\TestCall', $result);
}

function testCoversChaining(): void
{
    $result = it('does something', function (): void {})->covers('App\MyClass');
    assertType('Pest\PendingCalls\TestCall', $result);
}

function testPlatformSkipMethods(): void
{
    assertType('Pest\PendingCalls\TestCall', it('test', function (): void {})->skipOnWindows());
    assertType('Pest\PendingCalls\TestCall', it('test', function (): void {})->skipOnMac());
    assertType('Pest\PendingCalls\TestCall', it('test', function (): void {})->skipOnLinux());
    assertType('Pest\PendingCalls\TestCall', it('test', function (): void {})->onlyOnWindows());
    assertType('Pest\PendingCalls\TestCall', it('test', function (): void {})->onlyOnMac());
    assertType('Pest\PendingCalls\TestCall', it('test', function (): void {})->onlyOnLinux());
}
