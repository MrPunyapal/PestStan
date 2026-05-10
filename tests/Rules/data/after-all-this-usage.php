<?php

declare(strict_types=1);

// Errors: $this usage inside afterAll
afterAll(function (): void {
    $this->user = new stdClass; // line 7
});

afterAll(function (): void {
    $this->tearDownConnection(); // line 11
});

// Valid: $this inside afterEach (not static context)
afterEach(function (): void {
    $this->user = new stdClass;
});

// Valid: afterAll without $this
afterAll(function (): void {
    $db = new stdClass;
});
