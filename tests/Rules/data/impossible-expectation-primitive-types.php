<?php

declare(strict_types=1);

// Cross-file cache interaction: basic type expectations that should never be flagged as impossible.

it('string should not be impossible for toBeString', function (): void {
    expect('hello')->toBeString();
});

it('int should not be impossible for toBeInt', function (): void {
    expect(42)->toBeInt();
});

it('float should not be impossible for toBeFloat', function (): void {
    expect(3.14)->toBeFloat();
});

it('array should not be impossible for toBeArray', function (): void {
    expect([1, 2])->toBeArray();
});

it('null should not be impossible for toBeNull', function (): void {
    expect(null)->toBeNull();
});

it('object should not be impossible for toBeObject', function (): void {
    expect(new stdClass)->toBeObject();
});

it('callable should not be impossible for toBeCallable', function (): void {
    expect('strlen')->toBeCallable();
});
