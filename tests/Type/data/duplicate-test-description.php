<?php

declare(strict_types=1);

// Error: duplicate test description
it('does something', function () { // first occurrence, line 5
    expect(true)->toBeTrue();
});

it('does something', function () { // duplicate, line 9
    expect(true)->toBeTrue();
});

// Error: duplicate test() calls
test('another test', function () { // first occurrence, line 14
    expect(true)->toBeTrue();
});

test('another test', function () { // duplicate, line 18
    expect(true)->toBeTrue();
});

// Error: it() and test() with same effective description
test('it matches cross-function', function () { // first occurrence, line 23
    expect(true)->toBeTrue();
});

it('matches cross-function', function () { // duplicate (it() prepends "it "), line 27
    expect(true)->toBeTrue();
});

// Valid: different descriptions
it('first test', function () {
    expect(true)->toBeTrue();
});

it('second test', function () {
    expect(true)->toBeTrue();
});

test('third test', function () {
    expect(true)->toBeTrue();
});
