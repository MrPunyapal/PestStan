<?php

declare(strict_types=1);

use Pest\Expectation;
use PHPUnit\Framework\TestCase;

it('works with Pest functions', function (): void {
    expect(true)->toBe(true);
});

it('has $this bound to TestCase', function (): void {
    expect($this)->toBeInstanceOf(TestCase::class);
    $this->assertTrue(true);
});

test('expect function is available', function (): void {
    $value = 'test';
    $result = expect($value);

    expect($result)->toBeInstanceOf(Expectation::class);
});
