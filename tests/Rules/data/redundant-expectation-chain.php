<?php

declare(strict_types=1);

it('suppresses redundant assertions after invalid matcher requirements', function (): void {
    expect(123)->toHaveCount(2)->toBeInt();
});

it('still reports redundant assertions before valid follow-up matchers', function (): void {
    expect('abc')->toBeString()->toStartWith('a'); // line 10
});
