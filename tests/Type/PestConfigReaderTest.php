<?php

declare(strict_types=1);

namespace Tests\Type;

use PestStan\Type\Pest\PestConfigReader;
use PestStan\Type\Pest\PestFileDiscoverer;
use RuntimeException;
use Tests\Rules\Fixtures\DynamicTrait;
use Tests\Rules\Fixtures\MixedDynamicTrait;
use Tests\Rules\Fixtures\OtherTrait;
use Tests\Rules\Fixtures\RefreshDatabase;
use Tests\Type\Fixtures\CustomTestCase;
use Tests\Type\Fixtures\HelperTrait;

$makeReader = static function (): array {
    $fixtureDir = realpath(__DIR__ . '/Fixtures/pestconfig');

    if ($fixtureDir === false) {
        throw new RuntimeException('Fixture directory not found.');
    }

    $discoverer = new PestFileDiscoverer([$fixtureDir]);

    return [$fixtureDir, new PestConfigReader([], $discoverer)];
};

test('resolves extend binding for feature directory', function () use ($makeReader): void {
    [$fixtureDir, $reader] = $makeReader();

    $bindings = $reader->resolveBindings($fixtureDir . '/Feature/SomeTest.php');

    expect($bindings)->toContain(CustomTestCase::class);
});

test('resolves extend binding for unit directory', function () use ($makeReader): void {
    [$fixtureDir, $reader] = $makeReader();

    $bindings = $reader->resolveBindings($fixtureDir . '/Unit/SomeTest.php');

    expect($bindings)->toContain(CustomTestCase::class);
});

test('resolves use binding for helpers subdirectory', function () use ($makeReader): void {
    [$fixtureDir, $reader] = $makeReader();

    $bindings = $reader->resolveBindings($fixtureDir . '/Feature/Helpers/SomeTest.php');

    expect($bindings)->toContain(HelperTrait::class);
});

test('accumulates parent and subdirectory bindings', function () use ($makeReader): void {
    [$fixtureDir, $reader] = $makeReader();

    $bindings = $reader->resolveBindings($fixtureDir . '/Feature/Helpers/SomeTest.php');

    expect($bindings)
        ->toContain(CustomTestCase::class)
        ->toContain(HelperTrait::class);
});

test('returns empty for unmatched path', function () use ($makeReader): void {
    [, $reader] = $makeReader();

    $bindings = $reader->resolveBindings('/some/other/path/Test.php');

    expect($bindings)->toBeEmpty();
});

test('resolves statically known global uses and skips dynamic paths', function (): void {
    $fixtureDir = realpath(__DIR__ . '/../Rules/data/redundant-local-use');
    if ($fixtureDir === false) {
        throw new RuntimeException('Redundant local use fixture directory not found.');
    }

    $reader = new PestConfigReader([], new PestFileDiscoverer([$fixtureDir]));
    $bindings = $reader->resolveGlobalUses($fixtureDir . '/Feature/uses.php');

    expect(array_column($bindings, 'class'))
        ->toContain(RefreshDatabase::class)
        ->not->toContain(CustomTestCase::class)
        ->not->toContain(DynamicTrait::class)
        ->not->toContain(MixedDynamicTrait::class)
        ->and($reader->resolveBindings($fixtureDir . '/Feature/uses.php'))->toContain(CustomTestCase::class)
        ->toContain(RefreshDatabase::class)
        ->and(array_column($reader->resolveGlobalUses($fixtureDir . '/PluralFeature/multiple.php'), 'class'))
        ->toContain(OtherTrait::class)
        ->not->toContain(CustomTestCase::class)
        ->and(array_column($reader->resolveGlobalUses($fixtureDir . '/StandaloneFeature/uses.php'), 'class'))
        ->toContain(DynamicTrait::class);
});
