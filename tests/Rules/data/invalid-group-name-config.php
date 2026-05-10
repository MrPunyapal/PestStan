<?php

declare(strict_types=1);

use Tests\TestCase;

// Errors: empty group name on uses() chain
uses(TestCase::class)->group(''); // line 8

// Errors: whitespace-only group name on uses() chain
uses(TestCase::class)->group('  '); // line 11

// Errors: missing group name on uses() chain
uses(TestCase::class)->group(); // line 14

// Errors: empty group name on pest() configuration
pest()->group(''); // line 17

// Errors: whitespace-only group name on pest() configuration
pest()->group('  '); // line 20

// Errors: missing group name on pest() configuration
pest()->group(); // line 23

// Valid: non-empty group names
uses(TestCase::class)->group('feature');
pest()->group('integration');
pest()->extend(TestCase::class)->group('browser');
