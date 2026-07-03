<?php

declare(strict_types=1);

use Tests\Rules\Fixtures\DynamicTrait;
use Tests\Rules\Fixtures\MixedDynamicTrait;
use Tests\Rules\Fixtures\OtherTrait;
use Tests\Rules\Fixtures\RefreshDatabase;
use Tests\Type\Fixtures\CustomTestCase;

pest()
    ->extend(CustomTestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function (): void {
        // Shared test setup.
    })
    ->in('Feature');

pest()
    ->extends(CustomTestCase::class)
    ->uses(OtherTrait::class)
    ->in('PluralFeature');

uses(DynamicTrait::class)->in('StandaloneFeature');

$dynamicPath = 'Feature';
pest()->use(DynamicTrait::class)->in($dynamicPath);
pest()->use(DynamicTrait::class)->in(test_path('Feature'));
pest()->use(DynamicTrait::class)->in(__DIR__ . '/Feature');
pest()->use(DynamicTrait::class)->in(FEATURE_PATH);
pest()->use(MixedDynamicTrait::class)->in('Feature', $dynamicPath);
