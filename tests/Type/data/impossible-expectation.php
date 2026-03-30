<?php

declare(strict_types=1);

// Errors: impossible type assertions
it('int cannot be string', function (): void {
    expect(42)->toBeString(); // line 7
});

it('string cannot be int', function (): void {
    expect('hello')->toBeInt(); // line 11
});

it('string cannot be float', function (): void {
    expect('hello')->toBeFloat(); // line 15
});

it('string cannot be bool', function (): void {
    expect('hello')->toBeBool(); // line 19
});

it('int cannot be true', function (): void {
    expect(42)->toBeTrue(); // line 23
});

it('int cannot be false', function (): void {
    expect(42)->toBeFalse(); // line 27
});

it('string cannot be null', function (): void {
    expect('hello')->toBeNull(); // line 31
});

it('string cannot be array', function (): void {
    expect('hello')->toBeArray(); // line 35
});

it('int cannot be object', function (): void {
    expect(42)->toBeObject(); // line 39
});

it('int cannot be iterable', function (): void {
    expect(42)->toBeIterable(); // line 43
});

it('null cannot be callable', function (): void {
    expect()->toBeCallable(); // line 47
});

it('int cannot be instance of stdClass', function (): void {
    expect(42)->toBeInstanceOf(stdClass::class); // line 51
});

it('array cannot be scalar', function (): void {
    expect([])->toBeScalar(); // line 55
});

it('null cannot be numeric', function (): void {
    expect()->toBeNumeric(); // line 59
});

// Valid: compatible type assertions
it('string is string', function (): void {
    expect('hello')->toBeString();
});

it('int is int', function (): void {
    expect(42)->toBeInt();
});

it('mixed could be anything', function (): void {
    /** @var mixed $value */
    $value = getValue();
    expect($value)->toBeString()->toBeInt();
});

it('union type might match', function (): void {
    /** @var int|string $value */
    $value = getValue();
    expect($value)->toBeString()->toBeInt();
});

it('true is bool', function (): void {
    expect(true)->toBeBool();
});

it('null is null', function (): void {
    expect()->toBeNull();
});

it('array is array', function (): void {
    expect([1, 2])->toBeArray();
});

it('object is object', function (): void {
    expect(new stdClass)->toBeObject();
});

it('array is iterable', function (): void {
    expect([1, 2])->toBeIterable();
});

it('int is scalar', function (): void {
    expect(42)->toBeScalar();
});

it('int is numeric', function (): void {
    expect(42)->toBeNumeric();
});
