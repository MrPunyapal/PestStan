<?php

declare(strict_types=1);

use Tests\Rules\Fixtures\OtherTrait;
use Tests\Rules\Fixtures\RefreshDatabase;

uses(
    RefreshDatabase::class,
    OtherTrait::class,
);
