<?php

declare(strict_types=1);

use Pest\Mixins\Expectation;

// Errors: static closures
it('static closure in it', static function (): void { // line 5
    expect(true)->toBeTrue();
});

test('static closure in test', static function (): void { // line 9
    expect(true)->toBeTrue();
});

describe('static closure in describe', static function (): void { // line 13
    it('inner test', function (): void {
        expect(true)->toBeTrue();
    });
});

beforeEach(static function (): void { // line 19
    // setup
});

afterEach(static function (): void { // line 23
    // cleanup
});

beforeAll(static function (): void { // line 27
    // setup
});

afterAll(static function (): void { // line 31
    // cleanup
});

// Errors: static arrow functions
it('static arrow fn in it', static fn (): Expectation => expect(true)->toBeTrue()); // line 36

// Valid: non-static closures
it('non-static closure', function (): void {
    expect(true)->toBeTrue();
});

test('non-static closure', function (): void {
    expect(true)->toBeTrue();
});

beforeEach(function (): void {
    // setup
});

afterEach(function (): void {
    // cleanup
});

beforeAll(function (): void {
    // setup
});

afterAll(function (): void {
    // cleanup
});
