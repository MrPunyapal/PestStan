<?php

declare(strict_types=1);

describe('group with beforeAll', function () {
    beforeAll(function () { // line 6
        // setup
    });

    it('test inside describe', function () {
        expect(true)->toBeTrue();
    });
});

describe('group with afterAll', function () {
    afterAll(function () { // line 16
        // cleanup
    });

    it('test inside describe', function () {
        expect(true)->toBeTrue();
    });
});

describe('group with both', function () {
    beforeAll(function () { // line 26
        // setup
    });
    afterAll(function () { // line 29
        // cleanup
    });

    it('test inside describe', function () {
        expect(true)->toBeTrue();
    });
});

// Valid: beforeAll/afterAll at top level
beforeAll(function () {
    // setup
});

afterAll(function () {
    // cleanup
});

// Valid: beforeEach/afterEach inside describe
describe('group with hooks', function () {
    beforeEach(function () {
        // setup
    });

    afterEach(function () {
        // cleanup
    });

    it('test', function () {
        expect(true)->toBeTrue();
    });
});
