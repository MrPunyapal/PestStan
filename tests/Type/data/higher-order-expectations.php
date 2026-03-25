<?php

declare(strict_types=1);

namespace HigherOrderExpectations;

use Tests\Type\Fixtures\Author;
use Tests\Type\Fixtures\Post;

use function PHPStan\Testing\assertType;

function testPropertyAccessOnExpectation(): void
{
    $post = new Post;
    $result = expect($post)->title;
    assertType('Pest\Expectations\HigherOrderExpectation<Pest\Expectation<Tests\Type\Fixtures\Post>, string>', $result);
}

function testNestedPropertyAccess(): void
{
    $post = new Post;
    $result = expect($post)->editor;
    assertType('Pest\Expectations\HigherOrderExpectation<Pest\Expectation<Tests\Type\Fixtures\Post>, Tests\Type\Fixtures\Author|null>', $result);
}

function testChainedPropertyAfterAssertion(): void
{
    $post = new Post;
    $result = expect($post)
        ->title->toBe('Hello')
        ->content;
    assertType('Pest\Expectations\HigherOrderExpectation<Pest\Expectation<Tests\Type\Fixtures\Post>, string>', $result);
}

function testHigherOrderPropertyChain(): void
{
    $post = new Post;
    $result = expect($post)->author->name;
    assertType('Pest\Expectations\HigherOrderExpectation<Pest\Expectation<Tests\Type\Fixtures\Post>, string>', $result);
}

function testFullChainWithAssertions(): void
{
    $post = new Post;
    $result = expect($post)
        ->title->toBe('Title')
        ->content->toContain('content');
    assertType('Pest\Expectations\HigherOrderExpectation<Pest\Expectation<Tests\Type\Fixtures\Post>, Tests\Type\Fixtures\Post>', $result);
}

function testAndThenHigherOrder(): void
{
    $post = new Post;
    $result = expect(true)->toBeTrue()
        ->and($post)
        ->title;
    assertType('Pest\Expectations\HigherOrderExpectation<Pest\Expectation<Tests\Type\Fixtures\Post>, string>', $result);
}
