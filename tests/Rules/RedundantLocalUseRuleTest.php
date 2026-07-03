<?php

declare(strict_types=1);

namespace Tests\Rules;

use PestStan\Rules\RedundantLocalUseRule;
use PestStan\Type\Pest\PestConfigReader;
use PestStan\Type\Pest\PestFileDiscoverer;
use RuntimeException;
use Tests\RuleTestCase;

beforeAll(function (): void {
    RuleTestCase::$additionalConfigFiles = [
        __DIR__ . '/../extension.neon',
    ];

    $fixtureDir = realpath(__DIR__ . '/data/redundant-local-use');
    if ($fixtureDir === false) {
        throw new RuntimeException('Redundant local use fixture directory not found.');
    }

    $reader = new PestConfigReader([], new PestFileDiscoverer([$fixtureDir]));
    RuleTestCase::$rule = new RedundantLocalUseRule($reader);
});

test('redundant local uses trait is reported', function (): void {
    $this->analyse([__DIR__ . '/data/redundant-local-use/Feature/uses.php'], [
        [
            'RefreshDatabase is already applied globally through tests/Rules/data/redundant-local-use/Pest.php for this test file.',
            7,
        ],
    ]);
});

test('redundant local pest use trait is reported', function (): void {
    $this->analyse([__DIR__ . '/data/redundant-local-use/Feature/pest-use.php'], [
        [
            'RefreshDatabase is already applied globally through tests/Rules/data/redundant-local-use/Pest.php for this test file.',
            7,
        ],
    ]);
});

test('redundant local plural pest uses trait is reported', function (): void {
    $this->analyse([__DIR__ . '/data/redundant-local-use/Feature/pest-uses.php'], [
        [
            'RefreshDatabase is already applied globally through tests/Rules/data/redundant-local-use/Pest.php for this test file.',
            7,
        ],
    ]);
});

test('chained extend and use reports only the globally used item in a multi-class declaration', function (): void {
    $this->analyse([__DIR__ . '/data/redundant-local-use/Feature/multiple.php'], [
        [
            'RefreshDatabase is already applied globally through tests/Rules/data/redundant-local-use/Pest.php for this test file.',
            11,
        ],
    ]);
});

test('globally extended class is not treated as a redundant local use', function (): void {
    $this->analyse([__DIR__ . '/data/redundant-local-use/Feature/extend.php'], []);
});

test('global plural uses reports its class but not the plural extends class', function (): void {
    $this->analyse([__DIR__ . '/data/redundant-local-use/PluralFeature/multiple.php'], [
        [
            'OtherTrait is already applied globally through tests/Rules/data/redundant-local-use/Pest.php for this test file.',
            10,
        ],
    ]);
});

test('global standalone uses with a static path is supported', function (): void {
    $this->analyse([__DIR__ . '/data/redundant-local-use/StandaloneFeature/uses.php'], [
        [
            'DynamicTrait is already applied globally through tests/Rules/data/redundant-local-use/Pest.php for this test file.',
            7,
        ],
    ]);
});

test('file outside global path is not reported', function (): void {
    $this->analyse([__DIR__ . '/data/redundant-local-use/Unit/outside.php'], []);
});

test('dynamic computed and unknown global in paths are skipped', function (): void {
    $this->analyse([__DIR__ . '/data/redundant-local-use/Feature/dynamic-in.php'], []);
});

test('an in path list containing a dynamic item is skipped entirely', function (): void {
    $this->analyse([__DIR__ . '/data/redundant-local-use/Feature/mixed-dynamic-in.php'], []);
});

test('dynamic local use is skipped', function (): void {
    $this->analyse([__DIR__ . '/data/redundant-local-use/Feature/dynamic-local.php'], []);
});
