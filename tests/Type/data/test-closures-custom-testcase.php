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

function testProtectedMethodCallOnCustomTestCase(): void
{
    it('can call protected CustomTestCase methods on $this', function (): void {
        assertType('string', $this->createHelper());
    });
}

function testProtectedMethodCallFromParentOnCustomTestCase(): void
{
    it('can call protected parent TestCase methods on $this', function (): void {
        assertType('string', $this->getActualOutputForAssertion());
    });
}

function testTestCallMethodOnCustomTestCase(): void
{
    assertType('string', it('uses custom test case fluent methods')->publicHelper());
}
