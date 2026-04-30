<?php

declare(strict_types=1);

// Errors: $this usage inside beforeAll
beforeAll(function (): void {
    $this->user = new stdClass; // line 7
});

beforeAll(function (): void {
    $this->setup(); // line 11
});

// Valid: $this inside beforeEach (not static context)
beforeEach(function (): void {
    $this->user = new stdClass;
});

// Valid: beforeAll without $this
beforeAll(function (): void {
    $db = new stdClass;
});
