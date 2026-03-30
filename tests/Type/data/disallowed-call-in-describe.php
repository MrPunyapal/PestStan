<?php

declare(strict_types=1);

describe('group with beforeAll', function (): void {
    beforeAll(function (): void { // line 6
        // setup
    });

    it('test inside describe', function (): void {
        expect(true)->toBeTrue();
    });
});

describe('group with afterAll', function (): void {
    afterAll(function (): void { // line 16
        // cleanup
    });

    it('test inside describe', function (): void {
        expect(true)->toBeTrue();
    });
});

describe('group with both', function (): void {
    beforeAll(function (): void { // line 26
        // setup
    });
    afterAll(function (): void { // line 29
        // cleanup
    });

    it('test inside describe', function (): void {
        expect(true)->toBeTrue();
    });
});

// Valid: beforeAll/afterAll at top level
beforeAll(function (): void {
    // setup
});

afterAll(function (): void {
    // cleanup
});

// Valid: beforeEach/afterEach inside describe
describe('group with hooks', function (): void {
    beforeEach(function (): void {
        // setup
    });

    afterEach(function (): void {
        // cleanup
    });

    it('test', function (): void {
        expect(true)->toBeTrue();
    });
});
