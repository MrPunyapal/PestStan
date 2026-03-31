<?php

declare(strict_types=1);

// Errors: tests without assertions
it('does calculation', function (): void { // line 6
    $total = 1 + 2;
});

test('does setup only', function (): void { // line 10
    $user = new stdClass;
    $user->name = 'John';
});

// Valid: tests with expect()
it('has expect', function (): void {
    expect(true)->toBeTrue();
});

// Valid: tests with chained expect
it('has chained expect', function (): void {
    $x = 1;
    expect($x)->toBe(1);
});

// Valid: tests with $this->assert
it('has assert method', function (): void {
    $this->assertTrue(true);
});

// Valid: empty test closure (handled by EmptyTestClosureRule)
it('empty test', function (): void {});

// Valid: test with throws chain
it('with throws', function (): void {
    throw new RuntimeException('error');
})->throws(RuntimeException::class);
