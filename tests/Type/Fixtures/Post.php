<?php

declare(strict_types=1);

namespace Tests\Type\Fixtures;

class Post
{
    public string $title;

    public string $content;

    public Author $author;

    public ?Author $editor = null;

    public static function make(): static
    {
        return new static;
    }
}
