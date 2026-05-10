<?php

declare(strict_types=1);

// Errors: rector-pest-generated string matchers on non-string values
it('toBeUppercase on int', function (): void {
    expect(42)->toBeUppercase(); // line 7
});

it('toBeLowercase on int', function (): void {
    expect(42)->toBeLowercase(); // line 11
});

it('toBeAlphaNumeric on int', function (): void {
    expect(42)->toBeAlphaNumeric(); // line 15
});

it('toBeAlpha on int', function (): void {
    expect(42)->toBeAlpha(); // line 19
});

it('toBeSnakeCase on int', function (): void {
    expect(42)->toBeSnakeCase(); // line 23
});

it('toBeKebabCase on int', function (): void {
    expect(42)->toBeKebabCase(); // line 27
});

it('toBeCamelCase on int', function (): void {
    expect(42)->toBeCamelCase(); // line 31
});

it('toBeStudlyCase on int', function (): void {
    expect(42)->toBeStudlyCase(); // line 35
});

it('toBeUuid on int', function (): void {
    expect(42)->toBeUuid(); // line 39
});

it('toBeUrl on int', function (): void {
    expect(42)->toBeUrl(); // line 43
});

it('toBeSlug on int', function (): void {
    expect(42)->toBeSlug(); // line 47
});

// Valid: modern string matchers on strings
it('string matchers on strings', function (): void {
    expect('ABC')->toBeUppercase();
    expect('abc')->toBeLowercase();
    expect('abc123')->toBeAlphaNumeric();
    expect('abc')->toBeAlpha();
    expect('snake_case')->toBeSnakeCase();
    expect('kebab-case')->toBeKebabCase();
    expect('camelCase')->toBeCamelCase();
    expect('StudlyCase')->toBeStudlyCase();
    expect('550e8400-e29b-41d4-a716-446655440000')->toBeUuid();
    expect('https://pestphp.com')->toBeUrl();
    expect('some title')->toBeSlug();
});
