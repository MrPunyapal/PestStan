<?php

declare(strict_types=1);

use Countable;
use IteratorAggregate;
use RuntimeException;
use stdClass;
use Throwable;

it('stdClass cannot satisfy countable interface', function (): void {
    expect(new stdClass)->toBeInstanceOf(Countable::class); // line 14
});

it('runtime exception cannot be countable', function (): void {
    expect(new RuntimeException)->toBeInstanceOf(Countable::class); // line 18
});

it('bool cannot be numeric', function (): void {
    /** @var bool $value */
    $value = getValue();

    expect($value)->toBeNumeric(); // line 23
});

it('unrelated interfaces are not proven impossible', function (): void {
    /** @var IteratorAggregate $value */
    $value = new ArrayObject;

    expect($value)->toBeInstanceOf(Countable::class);
});

it('parent types may still satisfy child assertions', function (): void {
    /** @var Throwable $value */
    $value = new RuntimeException;

    expect($value)->toBeInstanceOf(RuntimeException::class);
});
