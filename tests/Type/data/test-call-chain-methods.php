<?php

declare(strict_types=1);

namespace TestCallChainMethods;

use Pest\ArchPresets\AbstractPreset;
use Pest\ArchPresets\Laravel;
use Pest\ArchPresets\Php;
use Pest\PendingCalls\TestCall;
use Pest\Preset;

use function PHPStan\Testing\assertType;

function testArchReturnsTestCall(): void
{
    assertType(TestCall::class, arch());
}

function testArchPresetReturnsPreset(): void
{
    assertType(Preset::class, arch()->preset());
}

function testArchPresetPhp(): void
{
    assertType(Php::class, arch()->preset()->php());
}

function testArchPresetSecurity(): void
{
    assertType(AbstractPreset::class, arch()->preset()->security());
}

function testArchPresetLaravel(): void
{
    assertType(Laravel::class, arch()->preset()->laravel());
}

function testTestCallPreset(): void
{
    assertType(Preset::class, it('test')->preset());
}

function testTestCallNote(): void
{
    assertType(TestCall::class, it('test')->note('important'));
}
