<?php

declare(strict_types=1);

use Tests\Rules\Fixtures\OtherTrait;
use Tests\Rules\Fixtures\RefreshDatabase;
use Tests\Type\Fixtures\CustomTestCase;

uses(
    CustomTestCase::class,
    RefreshDatabase::class,
    OtherTrait::class,
);
