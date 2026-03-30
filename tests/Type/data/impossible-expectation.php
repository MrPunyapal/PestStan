<?php

declare(strict_types=1);

// Errors: impossible type assertions
it('int cannot be string', function () {
    expect(42)->toBeString(); // line 7
});

it('string cannot be int', function () {
    expect('hello')->toBeInt(); // line 11
});

it('string cannot be float', function () {
    expect('hello')->toBeFloat(); // line 15
});

it('string cannot be bool', function () {
    expect('hello')->toBeBool(); // line 19
});

it('int cannot be true', function () {
    expect(42)->toBeTrue(); // line 23
});

it('int cannot be false', function () {
    expect(42)->toBeFalse(); // line 27
});

it('string cannot be null', function () {
    expect('hello')->toBeNull(); // line 31
});

it('string cannot be array', function () {
    expect('hello')->toBeArray(); // line 35
});

it('int cannot be object', function () {
    expect(42)->toBeObject(); // line 39
});

it('int cannot be iterable', function () {
    expect(42)->toBeIterable(); // line 43
});

it('null cannot be callable', function () {
    expect(null)->toBeCallable(); // line 47
});

it('int cannot be instance of stdClass', function () {
    expect(42)->toBeInstanceOf(\stdClass::class); // line 51
});

it('array cannot be scalar', function () {
    expect([])->toBeScalar(); // line 55
});

it('null cannot be numeric', function () {
    expect(null)->toBeNumeric(); // line 59
});

// Valid: compatible type assertions
it('string is string', function () {
    expect('hello')->toBeString();
});

it('int is int', function () {
    expect(42)->toBeInt();
});

it('mixed could be anything', function () {
    /** @var mixed $value */
    $value = getValue();
    expect($value)->toBeString();
    expect($value)->toBeInt();
});

it('union type might match', function () {
    /** @var int|string $value */
    $value = getValue();
    expect($value)->toBeString();
    expect($value)->toBeInt();
});

it('true is bool', function () {
    expect(true)->toBeBool();
});

it('null is null', function () {
    expect(null)->toBeNull();
});

it('array is array', function () {
    expect([1, 2])->toBeArray();
});

it('object is object', function () {
    expect(new \stdClass)->toBeObject();
});

it('array is iterable', function () {
    expect([1, 2])->toBeIterable();
});

it('int is scalar', function () {
    expect(42)->toBeScalar();
});

it('int is numeric', function () {
    expect(42)->toBeNumeric();
});
