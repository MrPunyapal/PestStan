<?php

declare(strict_types=1);

namespace ArchExpectations;

use Pest\Arch\Contracts\ArchExpectation;

use function PHPStan\Testing\assertType;

function testToUseTrait(): void
{
    $result = expect('App\Models\User')->toUseTrait('Illuminate\Database\Eloquent\SoftDeletes');
    assertType(ArchExpectation::class, $result);
}

function testToBeFinal(): void
{
    $result = expect('App\Actions')->toBeFinal();
    assertType(ArchExpectation::class, $result);
}

function testToBeReadonly(): void
{
    $result = expect('App\DTOs')->toBeReadonly();
    assertType(ArchExpectation::class, $result);
}

function testToUseStrictTypes(): void
{
    $result = expect('App')->toUseStrictTypes();
    assertType(ArchExpectation::class, $result);
}

function testToExtend(): void
{
    $result = expect('App\Models')->toExtend('Illuminate\Database\Eloquent\Model');
    assertType(ArchExpectation::class, $result);
}

function testToImplement(): void
{
    $result = expect('App\Contracts')->toImplement('App\Contracts\BaseInterface');
    assertType(ArchExpectation::class, $result);
}

function testToUse(): void
{
    $result = expect('App\Models')->toUse('Illuminate\Database\Eloquent');
    assertType(ArchExpectation::class, $result);
}

function testToUseNothing(): void
{
    $result = expect('App\DTOs')->toUseNothing();
    assertType(ArchExpectation::class, $result);
}

function testToHavePrefix(): void
{
    $result = expect('App\Actions')->toHavePrefix('App\Actions');
    assertType(ArchExpectation::class, $result);
}

function testToHaveSuffix(): void
{
    $result = expect('App\Actions')->toHaveSuffix('Action');
    assertType(ArchExpectation::class, $result);
}

function testIgnoringChaining(): void
{
    $result = expect('App')->toUse('Illuminate')->ignoring('App\Legacy');
    assertType(ArchExpectation::class, $result);
}

function testIgnoringGlobalFunctions(): void
{
    $result = expect('App')->toUseNothing()->ignoringGlobalFunctions();
    assertType(ArchExpectation::class, $result);
}

function testPendingArchChaining(): void
{
    $result = expect('App')->classes()->toBeFinal();
    assertType(ArchExpectation::class, $result);
}

function testToBeEnum(): void
{
    $result = expect('App\Enums')->toBeEnum();
    assertType(ArchExpectation::class, $result);
}

function testToBeInterface(): void
{
    $result = expect('App\Contracts')->toBeInterface();
    assertType(ArchExpectation::class, $result);
}

function testToHaveConstructor(): void
{
    $result = expect('App\Services')->toHaveConstructor();
    assertType(ArchExpectation::class, $result);
}

function testToBeInvokable(): void
{
    $result = expect('App\Actions')->toBeInvokable();
    assertType(ArchExpectation::class, $result);
}
