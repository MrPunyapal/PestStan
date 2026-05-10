<?php

declare(strict_types=1);

it('reports only the first redundant semantic assertion on already precise values', function (): void {
    expect('abc')->toBeString()->toBeScalar(); // line 6
});

it('suppresses redundancies that are only introduced by earlier semantic narrowing', function (): void {
    /** @var int|string $value */
    $value = 'abc';

    expect($value)->toBeString()->toBeScalar();
});
