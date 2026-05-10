<?php

declare(strict_types=1);

use ArrayIterator;
use ArrayObject;

it('toHaveCount on string', function (): void {
    expect('foo')->toHaveCount(2); // line 9
});

it('toHaveSameSize on int', function (): void {
    expect(123)->toHaveSameSize([1, 2]); // line 13
});

it('each on int', function (): void {
    expect(123)->each(fn ($item) => $item); // line 17
});

it('sequence on int', function (): void {
    expect(123)->sequence(fn ($item) => $item->toBe(1)); // line 21
});

it('suppresses follow-up matcher checks after impossible assertion', function (): void {
    expect(123)->toBeString()->toHaveCount(2);
});

it('still validates follow-up matchers after redundant assertion', function (): void {
    expect('abc')->toBeString()->toHaveCount(2); // line 29
});

it('toHaveCount on array', function (): void {
    expect([1, 2])->toHaveCount(2);
});

it('toHaveCount on array iterator', function (): void {
    expect(new ArrayIterator([1]))->toHaveCount(1);
});

it('toHaveSameSize on array object', function (): void {
    expect(new ArrayObject([1]))->toHaveSameSize([1]);
});

it('each on array', function (): void {
    expect([1, 2])->each();
});

it('sequence on array', function (): void {
    expect([1, 2])->sequence(fn ($item) => $item->toBeInt());
});

it('propagates array state into each', function (): void {
    /** @var array<string, int>|string $value */
    $value = ['a' => 1];

    expect($value)->toBeArray()->each();
});

it('propagates instance-of state into countable matchers', function (): void {
    /** @var ArrayIterator<int, int>|stdClass $value */
    $value = new ArrayIterator([1]);

    expect($value)->toBeInstanceOf(ArrayIterator::class)->toHaveCount(1);
});

it('propagates fluent value changes into iterable matchers', function (): void {
    expect('seed')->and([1, 2])->each();
});

it('propagates json chains into countable matchers', function (): void {
    expect('{"a":1,"b":2}')->json()->toHaveCount(2);
});
