<?php

declare(strict_types=1);

// Errors: each() on non-iterable
it('each on int', function (): void {
    expect(42)->each(); // line 7
});

it('each on string', function (): void {
    expect('hello')->each(); // line 11
});

// Errors: sequence() on non-iterable
it('sequence on int', function (): void {
    expect(42)->sequence(fn ($item) => $item->toBe(1)); // line 16
});

// Errors: json() on non-string
it('json on int', function (): void {
    expect(42)->json(); // line 21
});

it('json on array', function (): void {
    expect([1, 2])->json(); // line 25
});

// Errors: string methods on non-string
it('toStartWith on int', function (): void {
    expect(42)->toStartWith('he'); // line 30
});

it('toEndWith on int', function (): void {
    expect(42)->toEndWith('lo'); // line 34
});

it('toBeJson on int', function (): void {
    expect(42)->toBeJson(); // line 38
});

it('toBeFile on int', function (): void {
    expect(42)->toBeFile(); // line 42
});

it('toBeDirectory on int', function (): void {
    expect(42)->toBeDirectory(); // line 46
});

// Valid: compatible types
it('each on array', function (): void {
    expect([1, 2, 3])->each();
});

it('sequence on array', function (): void {
    expect([1, 2])->sequence(fn ($item) => $item->toBe(1));
});

it('json on string', function (): void {
    expect('{"key": "value"}')->json();
});

it('toStartWith on string', function (): void {
    expect('hello')->toStartWith('he');
});

it('toBeJson on string', function (): void {
    expect('{}')->toBeJson();
});

it('toBeFile on string', function (): void {
    expect('/tmp/file.txt')->toBeFile();
});

it('each on mixed', function (): void {
    /** @var mixed $value */
    $value = getValue();
    expect($value)->each();
});
