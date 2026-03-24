<?php

declare(strict_types=1);

use Tests\Type\Fixtures\CustomTestCase;

it('can call protected child class methods on $this in Pest it() closure', function (): void {
    $this->createHelper();
    $this->getActualOutputForAssertion();
});

test('can call protected child class methods on $this in Pest test() closure', function (): void {
    $this->createHelper();
    $this->getActualOutputForAssertion();
});
