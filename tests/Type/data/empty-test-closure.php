<?php

declare(strict_types=1);

it('empty it closure', function () {
});

test('empty test closure', function () {
});

// Valid: non-empty closures
it('has assertions', function () {
    expect(true)->toBeTrue();
});

test('has assertions', function () {
    expect(42)->toBeInt();
});

// Valid: no closure argument
todo('implement later');

// Valid: test() without closure (higher-order)
test('without closure');
