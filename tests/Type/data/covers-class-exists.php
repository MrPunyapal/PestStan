<?php

declare(strict_types=1);

use Tests\Type\Fixtures\Post;

// Errors: coversClass with non-existent class
it('covers non-existent', function (): void {
    expect(true)->toBeTrue();
})->coversClass('App\NonExistent\FakeClass'); // line 10

// Valid: coversClass with existing class
it('covers existing', function (): void {
    expect(true)->toBeTrue();
})->coversClass(Post::class);
