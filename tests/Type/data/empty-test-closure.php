<?php

declare(strict_types=1);

it('empty it closure', function (): void {});

test('empty test closure', function (): void {});

// Valid: non-empty closures
it('has assertions', function (): void {
    expect(true)->toBeTrue();
});

test('has assertions', function (): void {
    expect(42)->toBeInt();
});

// Valid: no closure argument
todo('implement later');

// Valid: test() without closure (higher-order)
test('without closure');
