<?php

declare(strict_types=1);

use Tests\Rules\Fixtures\DynamicTrait;
use Tests\Rules\Fixtures\RefreshDatabase;

pest()->use(RefreshDatabase::class)->in('Feature');

$dynamicPath = 'Feature';
pest()->use(DynamicTrait::class)->in($dynamicPath);
