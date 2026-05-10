<?php

declare(strict_types=1);

it('reports only the first impossible assertion in a broken chain', function (): void {
    expect(123)->toBeString()->toBeArray(); // line 6
});

it('suppresses impossible assertions after an invalid matcher requirement', function (): void {
    expect(123)->toHaveCount(2)->toBeArray();
});

it('keeps valid chains analyzable', function (): void {
    expect([1, 2])->toBeArray()->toHaveCount(2);
});

it('keeps multiline fluent chains analyzable', function (): void {
    expect('{"users":[1,2]}')
        ->json()
        ->toBeArray()
        ->toHaveCount(1);
});

it('keeps nested expectation chains analyzable', function (): void {
    expect([[1], [2]])
        ->toBeArray()
        ->each(fn (array $row) => expect($row)->toBeArray()->toHaveCount(1));
});
