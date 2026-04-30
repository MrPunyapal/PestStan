<?php

declare(strict_types=1);

// Errors: empty describe block
describe('empty group', function (): void { // line 6
});

// Errors: describe with only hooks (no tests)
describe('hooks only', function (): void { // line 10
    beforeEach(function (): void {
        // setup
    });
});

// Errors: describe with chained hooks only (no tests)
describe('hooks with chain only', function (): void { // line 17
    beforeEach(function (): void {
        // setup
    })->skip();
});

// Valid: describe with tests
describe('valid group', function (): void {
    it('has a test', function (): void {
        expect(true)->toBeTrue();
    });
});

// Valid: describe with nested describe
describe('nested group', function (): void {
    describe('inner', function (): void {
        it('inner test', function (): void {
            expect(true)->toBeTrue();
        });
    });
});

// valid: describe with throwsNoExceptions
describe('test something', function () {
    it('throws no exceptions', function () {
        $result = 1 + 1;
    })->throwsNoExceptions();
});

// valid: describe with todo test
describe('todo test', function (): void {
    todo('todo');
});
