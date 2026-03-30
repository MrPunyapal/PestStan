<?php

declare(strict_types=1);

// Error: duplicate test description
it('does something', function (): void { // first occurrence, line 5
    expect(true)->toBeTrue();
});

it('does something', function (): void { // duplicate, line 9
    expect(true)->toBeTrue();
});

// Error: duplicate test() calls
test('another test', function (): void { // first occurrence, line 14
    expect(true)->toBeTrue();
});

test('another test', function (): void { // duplicate, line 18
    expect(true)->toBeTrue();
});

// Error: it() and test() with same effective description
test('it matches cross-function', function (): void { // first occurrence, line 23
    expect(true)->toBeTrue();
});

it('matches cross-function', function (): void { // duplicate (it() prepends "it "), line 27
    expect(true)->toBeTrue();
});

// Valid: different descriptions
it('first test', function (): void {
    expect(true)->toBeTrue();
});

it('second test', function (): void {
    expect(true)->toBeTrue();
});

test('third test', function (): void {
    expect(true)->toBeTrue();
});
