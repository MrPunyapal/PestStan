<?php

declare(strict_types=1);

namespace TestClosuresCustomTestCase;

use function PHPStan\Testing\assertType;

use Tests\Type\Fixtures\CustomTestCase;

function testThisTypeInItWithCustomTestCase(): void
{
    it('has custom $this type', function (): void {
        assertType(CustomTestCase::class, $this);
    });
}

function testThisTypeInTestWithCustomTestCase(): void
{
    test('has custom $this type', function (): void {
        assertType(CustomTestCase::class, $this);
    });
}

function testThisTypeInBeforeEachWithCustomTestCase(): void
{
    beforeEach(function (): void {
        assertType(CustomTestCase::class, $this);
    });
}

function testDynamicPropertyWithCustomTestCase(): void
{
    it('allows dynamic properties on custom $this', function (): void {
        assertType('mixed', $this->customProp);
    });
}
