<?php

declare(strict_types=1);

// Errors: redundant type assertions (always pass)
it('true is always toBeTrue', function (): void {
    expect(true)->toBeTrue(); // line 7
});

it('false is always toBeFalse', function (): void {
    expect(false)->toBeFalse(); // line 11
});

it('true is always toBeBool', function (): void {
    expect(true)->toBeBool(); // line 15
});

it('string literal is always toBeString', function (): void {
    expect('hello')->toBeString(); // line 19
});

it('int literal is always toBeInt', function (): void {
    expect(42)->toBeInt(); // line 23
});

it('float literal is always toBeFloat', function (): void {
    expect(3.14)->toBeFloat(); // line 27
});

it('null is always toBeNull', function (): void {
    expect()->toBeNull(); // line 31
});

it('array is always toBeArray', function (): void {
    expect([])->toBeArray(); // line 35
});

it('string is always toBeScalar', function (): void {
    expect('hello')->toBeScalar(); // line 39
});

it('int is always toBeNumeric', function (): void {
    expect(42)->toBeNumeric(); // line 43
});

it('stdClass instance is always toBeInstanceOf stdClass', function (): void {
    expect(new stdClass)->toBeInstanceOf(stdClass::class); // line 47
});

// Valid: non-trivial assertions
it('mixed could be anything', function (): void {
    /** @var mixed $value */
    $value = getValue();
    expect($value)->toBeTrue();
});

it('union type might not match', function (): void {
    /** @var int|string $value */
    $value = getValue();
    expect($value)->toBeInt();
});

it('bool might not be true', function (): void {
    /** @var bool $value */
    $value = getValue();
    expect($value)->toBeTrue();
});
