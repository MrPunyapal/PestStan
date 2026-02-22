<?php

declare(strict_types=1);

namespace TestClosures;

use function PHPStan\Testing\assertType;

use PHPUnit\Framework\TestCase;

function testThisTypeInIt(): void
{
    it('has correct $this type', function (): void {
        assertType(TestCase::class, $this);
    });
}

function testThisTypeInTest(): void
{
    test('has correct $this type', function (): void {
        assertType(TestCase::class, $this);
    });
}

function testThisTypeInBeforeEach(): void
{
    beforeEach(function (): void {
        assertType(TestCase::class, $this);
    });
}

function testThisTypeInAfterEach(): void
{
    afterEach(function (): void {
        assertType(TestCase::class, $this);
    });
}

function testThisTypeInBeforeAll(): void
{
    beforeAll(function (): void {
        assertType(TestCase::class, $this);
    });
}

function testThisTypeInAfterAll(): void
{
    afterAll(function (): void {
        assertType(TestCase::class, $this);
    });
}

function testThisTypeInDescribe(): void
{
    describe('group', function (): void {
        assertType(TestCase::class, $this);
    });
}
