<?php

declare(strict_types=1);

use Tests\TestCase;

test('expect function types', function (string $assertType, string $file, mixed ...$args): void {
    $this->assertFileAsserts($assertType, $file, ...$args);
})->with(function (): Iterator {
    yield from TestCase::gatherAssertTypes(__DIR__ . '/data/expect-function.php');
});

test('test closure types', function (string $assertType, string $file, mixed ...$args): void {
    $this->assertFileAsserts($assertType, $file, ...$args);
})->with(function (): Iterator {
    yield from TestCase::gatherAssertTypes(__DIR__ . '/data/test-closures.php');
});

test('expectation method types', function (string $assertType, string $file, mixed ...$args): void {
    $this->assertFileAsserts($assertType, $file, ...$args);
})->with(function (): Iterator {
    yield from TestCase::gatherAssertTypes(__DIR__ . '/data/expectation-methods.php');
});

test('test call method types', function (string $assertType, string $file, mixed ...$args): void {
    $this->assertFileAsserts($assertType, $file, ...$args);
})->with(function (): Iterator {
    yield from TestCase::gatherAssertTypes(__DIR__ . '/data/test-call-methods.php');
});

test('pest function types', function (string $assertType, string $file, mixed ...$args): void {
    $this->assertFileAsserts($assertType, $file, ...$args);
})->with(function (): Iterator {
    yield from TestCase::gatherAssertTypes(__DIR__ . '/data/pest-functions.php');
});

test('arch expectation types', function (string $assertType, string $file, mixed ...$args): void {
    $this->assertFileAsserts($assertType, $file, ...$args);
})->with(function (): Iterator {
    yield from TestCase::gatherAssertTypes(__DIR__ . '/data/arch-expectations.php');
});
