<?php

declare(strict_types=1);

it('suppresses downstream matcher requirements after an impossible assertion', function (): void {
    expect(123)->toBeString()->json()->toHaveCount(1); // line 6
});

it('suppresses downstream iterable transformations after an impossible assertion', function (): void {
    expect(123)->toBeString()->and([1, 2])->each(); // line 10
});
