<?php

declare(strict_types=1);

// Errors: empty group name
it('empty group name', function (): void { // line 6
    expect(true)->toBeTrue();
})->group('');

// Errors: whitespace-only group name
it('whitespace group name', function (): void { // line 11
    expect(true)->toBeTrue();
})->group('  ');

// Valid: non-empty group name
it('valid group name', function (): void {
    expect(true)->toBeTrue();
})->group('feature');

// Valid: multiple groups, all non-empty
it('multiple groups', function (): void {
    expect(true)->toBeTrue();
})->group('feature', 'integration');
