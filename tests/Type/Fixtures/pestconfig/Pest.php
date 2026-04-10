<?php

declare(strict_types=1);

use Tests\Type\Fixtures\CustomTestCase;
use Tests\Type\Fixtures\HelperTrait;

pest()->extend(CustomTestCase::class)->in('Feature', 'Unit');
pest()->use(HelperTrait::class)->group('helper')->in('Feature/Helpers');
