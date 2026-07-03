<?php

declare(strict_types=1);

use Tests\Rules\Fixtures\OtherTrait;
use Tests\Type\Fixtures\CustomTestCase;

uses(
    CustomTestCase::class,
    OtherTrait::class,
);
