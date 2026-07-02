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

test('only redundant items in a multi-trait declaration are reported', function (): void {
    $this->analyse([__DIR__ . '/data/redundant-local-use/Feature/multiple.php'], [
        [
            'RefreshDatabase is already applied globally through tests/Rules/data/redundant-local-use/Pest.php for this test file.',
            9,
        ],
    ]);
});

test('file outside global path is not reported', function (): void {
    $this->analyse([__DIR__ . '/data/redundant-local-use/Unit/outside.php'], []);
});

test('dynamic global in path is skipped', function (): void {
    $this->analyse([__DIR__ . '/data/redundant-local-use/Feature/dynamic-in.php'], []);
});

test('dynamic local use is skipped', function (): void {
    $this->analyse([__DIR__ . '/data/redundant-local-use/Feature/dynamic-local.php'], []);
});
