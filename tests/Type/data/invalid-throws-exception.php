<?php

declare(strict_types=1);

// Errors: throws() with non-Throwable class
it('throws non-throwable', function (): void { // line 6
    throw new RuntimeException('error');
})->throws(stdClass::class);

// Valid: throws() with Throwable class
it('throws exception', function (): void {
    throw new RuntimeException('error');
})->throws(RuntimeException::class);

// Valid: throws() with base Exception
it('throws base exception', function (): void {
    throw new Exception('error');
})->throws(Exception::class);

// Valid: throws() with no arguments
it('throws anything', function (): void {
    throw new RuntimeException('error');
})->throws();
