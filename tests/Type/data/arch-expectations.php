<?php

declare(strict_types=1);

namespace ArchExpectations;

use function PHPStan\Testing\assertType;

function testToUseTrait(): void
{
    $result = expect('App\Models\User')->toUseTrait('Illuminate\Database\Eloquent\SoftDeletes');
    assertType('Pest\Arch\Contracts\ArchExpectation', $result);
}

function testToBeFinal(): void
{
    $result = expect('App\Actions')->toBeFinal();
    assertType('Pest\Arch\Contracts\ArchExpectation', $result);
}

function testToBeReadonly(): void
{
    $result = expect('App\DTOs')->toBeReadonly();
    assertType('Pest\Arch\Contracts\ArchExpectation', $result);
}

function testToUseStrictTypes(): void
{
    $result = expect('App')->toUseStrictTypes();
    assertType('Pest\Arch\Contracts\ArchExpectation', $result);
}

function testToExtend(): void
{
    $result = expect('App\Models')->toExtend('Illuminate\Database\Eloquent\Model');
    assertType('Pest\Arch\Contracts\ArchExpectation', $result);
}

function testToImplement(): void
{
    $result = expect('App\Contracts')->toImplement('App\Contracts\BaseInterface');
    assertType('Pest\Arch\Contracts\ArchExpectation', $result);
}

function testToUse(): void
{
    $result = expect('App\Models')->toUse('Illuminate\Database\Eloquent');
    assertType('Pest\Arch\Contracts\ArchExpectation', $result);
}

function testToUseNothing(): void
{
    $result = expect('App\DTOs')->toUseNothing();
    assertType('Pest\Arch\Contracts\ArchExpectation', $result);
}

function testToHavePrefix(): void
{
    $result = expect('App\Actions')->toHavePrefix('App\Actions');
    assertType('Pest\Arch\Contracts\ArchExpectation', $result);
}

function testToHaveSuffix(): void
{
    $result = expect('App\Actions')->toHaveSuffix('Action');
    assertType('Pest\Arch\Contracts\ArchExpectation', $result);
}

function testIgnoringChaining(): void
{
    $result = expect('App')->toUse('Illuminate')->ignoring('App\Legacy');
    assertType('Pest\Arch\Contracts\ArchExpectation', $result);
}

function testIgnoringGlobalFunctions(): void
{
    $result = expect('App')->toUseNothing()->ignoringGlobalFunctions();
    assertType('Pest\Arch\Contracts\ArchExpectation', $result);
}

function testPendingArchChaining(): void
{
    $result = expect('App')->classes()->toBeFinal();
    assertType('Pest\Arch\Contracts\ArchExpectation', $result);
}

function testToBeEnum(): void
{
    $result = expect('App\Enums')->toBeEnum();
    assertType('Pest\Arch\Contracts\ArchExpectation', $result);
}

function testToBeInterface(): void
{
    $result = expect('App\Contracts')->toBeInterface();
    assertType('Pest\Arch\Contracts\ArchExpectation', $result);
}

function testToHaveConstructor(): void
{
    $result = expect('App\Services')->toHaveConstructor();
    assertType('Pest\Arch\Contracts\ArchExpectation', $result);
}

function testToBeInvokable(): void
{
    $result = expect('App\Actions')->toBeInvokable();
    assertType('Pest\Arch\Contracts\ArchExpectation', $result);
}
