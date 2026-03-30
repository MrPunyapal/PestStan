<?php

declare(strict_types=1);

// Errors: static closures
it('static closure in it', static function () { // line 5
    expect(true)->toBeTrue();
});

test('static closure in test', static function () { // line 9
    expect(true)->toBeTrue();
});

describe('static closure in describe', static function () { // line 13
    it('inner test', function () {
        expect(true)->toBeTrue();
    });
});

beforeEach(static function () { // line 19
    // setup
});

afterEach(static function () { // line 23
    // cleanup
});

beforeAll(static function () { // line 27
    // setup
});

afterAll(static function () { // line 31
    // cleanup
});

// Errors: static arrow functions
it('static arrow fn in it', static fn () => expect(true)->toBeTrue()); // line 36

// Valid: non-static closures
it('non-static closure', function () {
    expect(true)->toBeTrue();
});

test('non-static closure', function () {
    expect(true)->toBeTrue();
});

beforeEach(function () {
    // setup
});

afterEach(function () {
    // cleanup
});

beforeAll(function () {
    // setup
});

afterAll(function () {
    // cleanup
});
