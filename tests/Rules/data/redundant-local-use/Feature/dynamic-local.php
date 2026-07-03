<?php

declare(strict_types=1);

use Tests\Rules\Fixtures\RefreshDatabase;

$trait = RefreshDatabase::class;
uses($trait);
pest()->use($trait);
pest()->uses($trait);
