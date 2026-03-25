<?php

declare(strict_types=1);

it('can call protected methods on $this in Pest it() closure', function (): void {
    $this->getActualOutputForAssertion();
    $this->setUp();
});

test('can call protected methods on $this in Pest test() closure', function (): void {
    $this->getActualOutputForAssertion();
    $this->setUp();
});

beforeEach(function (): void {
    $this->getActualOutputForAssertion();
});

afterEach(function (): void {
    $this->getActualOutputForAssertion();
});
