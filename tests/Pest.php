<?php

declare(strict_types=1);

use Tests\RuleTestCase;
use Tests\TestCase;

pest()->extend(TestCase::class)->in(__DIR__ . '/Type');
pest()->extend(RuleTestCase::class)->in(__DIR__ . '/Rules');
