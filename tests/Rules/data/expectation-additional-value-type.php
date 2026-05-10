<?php

declare(strict_types=1);

// Errors: iterable-only matchers on non-iterable values
it('toContainEqual on int', function (): void {
    expect(42)->toContainEqual(1); // line 7
});

it('toContainOnlyInstancesOf on string', function (): void {
    expect('hello')->toContainOnlyInstancesOf(stdClass::class); // line 11
});

// Errors: string-only matchers on non-string values
it('toBeDigits on int', function (): void {
    expect(42)->toBeDigits(); // line 16
});

it('toMatch on int', function (): void {
    expect(42)->toMatch('/\\d+/'); // line 20
});

// Valid: compatible values
it('iterable and string-only matchers on compatible values', function (): void {
    expect([1, 2])->toContainEqual(1);
    expect([new stdClass, new stdClass])->toContainOnlyInstancesOf(stdClass::class);
    expect('123')->toBeDigits();
    expect('hello')->toMatch('/^he/');
});
