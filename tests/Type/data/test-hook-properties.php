<?php

declare(strict_types=1);

namespace TestHookProperties;

use function PHPStan\Testing\assertType;

use Tests\Type\Fixtures\Author;
use Tests\Type\Fixtures\Post;

function testBeforeEachNewObject(): void
{
    beforeEach(function (): void {
        $this->post = new Post;
    });

    it('resolves property type from beforeEach', function (): void {
        assertType(Post::class, $this->post);
    });
}

function testBeforeEachMultipleProperties(): void
{
    beforeEach(function (): void {
        $this->post = new Post;
        $this->author = new Author;
    });

    it('resolves multiple property types', function (): void {
        assertType(Post::class, $this->post);
        assertType(Author::class, $this->author);
    });
}

function testBeforeAllNewObject(): void
{
    beforeAll(function (): void {
        $this->post = new Post;
    });

    it('resolves property type from beforeAll', function (): void {
        assertType(Post::class, $this->post);
    });
}

function testBeforeEachStringLiteral(): void
{
    beforeEach(function (): void {
        $this->name = 'test';
    });

    it('resolves string type from beforeEach', function (): void {
        assertType("'test'", $this->name);
    });
}

function testBeforeEachIntLiteral(): void
{
    beforeEach(function (): void {
        $this->count = 42;
    });

    it('resolves int type from beforeEach', function (): void {
        assertType('42', $this->count);
    });
}

function testBeforeEachBoolLiteral(): void
{
    beforeEach(function (): void {
        $this->flag = true;
    });

    it('resolves bool type from beforeEach', function (): void {
        assertType('true', $this->flag);
    });
}

function testBeforeEachNullLiteral(): void
{
    beforeEach(function (): void {
        $this->empty = null;
    });

    it('resolves null type from beforeEach', function (): void {
        assertType('null', $this->empty);
    });
}

function testBeforeEachArrayLiteral(): void
{
    beforeEach(function (): void {
        $this->items = [];
    });

    it('resolves array type from beforeEach', function (): void {
        assertType('array{}', $this->items);
    });
}

function testUnknownPropertyStaysMixed(): void
{
    beforeEach(function (): void {
        $this->post = new Post;
    });

    it('returns mixed for properties not set in hooks', function (): void {
        assertType('mixed', $this->unknownProp);
    });
}

function testUnrecognizedExpressionStaysMixed(): void
{
    beforeEach(function (): void {
        $this->result = someFunction();
    });

    it('returns mixed for unrecognized expressions', function (): void {
        assertType('mixed', $this->result);
    });
}

function testDescribeScopedBeforeEach(): void
{
    describe('group', function (): void {
        beforeEach(function (): void {
            $this->post = new Post;
        });

        it('resolves property from describe-scoped beforeEach', function (): void {
            assertType(Post::class, $this->post);
        });
    });
}

function testMultipleHooksSameProperty(): void
{
    beforeEach(function (): void {
        $this->item = new Post;
    });

    beforeEach(function (): void {
        $this->item = new Author;
    });

    it('unions types when multiple hooks set same property', function (): void {
        assertType('Tests\Type\Fixtures\Author|Tests\Type\Fixtures\Post', $this->item);
    });
}

function testBeforeEachFloatLiteral(): void
{
    beforeEach(function (): void {
        $this->price = 9.99;
    });

    it('resolves float type from beforeEach', function (): void {
        assertType('9.99', $this->price);
    });
}

function testBeforeEachVarAnnotation(): void
{
    beforeEach(function (): void {
        $this->post = Post::factory()->create();
    });

    it('resolves property type from @var PHPDoc annotation', function (): void {
        assertType(Post::class, $this->post);
    });
}

function testBeforeEachVarAnnotationWithoutVarName(): void
{
    beforeEach(function (): void {
        $this->author = Author::factory()->create();
    });

    it('resolves property type from @var PHPDoc without variable name', function (): void {
        assertType(Author::class, $this->author);
    });
}

function testBeforeEachVarAnnotationOnLocalVar(): void
{
    beforeEach(function (): void {
        /** @var Post $post */
        $post = Post::factory()->create();
        $this->post = $post;
    });

    it('resolves property type from @var-annotated local variable', function (): void {
        assertType(Post::class, $this->post);
    });
}

function testBeforeEachMethodCallChain(): void
{
    beforeEach(function (): void {
        $this->post = Post::make();
    });

    it('resolves property type from method call chain without annotation', function (): void {
        assertType(Post::class, $this->post);
    });
}
