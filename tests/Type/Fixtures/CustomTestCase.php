<?php

declare(strict_types=1);

namespace Tests\Type\Fixtures;

use PHPUnit\Framework\TestCase;

class CustomTestCase extends TestCase
{
    public function publicHelper(): string
    {
        return 'helper';
    }

    protected function createHelper(): string
    {
        return 'helper';
    }
}
