<?php

declare(strict_types=1);

// Error: repeat with 0
it('repeat zero', function (): void {
    expect(true)->toBeTrue();
})->repeat(0); // line 8

// Error: repeat with negative
it('repeat negative', function (): void {
    expect(true)->toBeTrue();
})->repeat(-1); // line 13

// Valid: repeat with positive values
it('repeat once', function (): void {
    expect(true)->toBeTrue();
})->repeat(1);

it('repeat three times', function (): void {
    expect(true)->toBeTrue();
})->repeat(3);

it('repeat hundred', function (): void {
    expect(true)->toBeTrue();
})->repeat(100);
