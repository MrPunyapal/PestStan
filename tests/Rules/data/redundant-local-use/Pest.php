<?php

declare(strict_types=1);

use Tests\Rules\Fixtures\DynamicTrait;
use Tests\Rules\Fixtures\RefreshDatabase;
use Tests\Type\Fixtures\CustomTestCase;

pest()
    ->extend(CustomTestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

$dynamicPath = 'Feature';
pest()->use(DynamicTrait::class)->in($dynamicPath);
