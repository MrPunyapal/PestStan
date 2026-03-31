<?php

declare(strict_types=1);

// Errors: todo() tests with closure body
it('has closure and todo', function (): void { // line 6
    expect(true)->toBeTrue();
})->todo();

test('has closure and todo', function (): void { // line 10
    $x = 1 + 2;
})->todo();

// Valid: todo() without closure body
it('no closure todo')->todo();

// Valid: test with closure but NOT todo
it('valid test', function (): void {
    expect(true)->toBeTrue();
});
