<?php

declare(strict_types=1);

namespace Tests\Type;

use PestStan\Type\Pest\PestConfigReader;
use PestStan\Type\Pest\PestFileDiscoverer;
use PHPUnit\Framework\TestCase;
use Tests\Type\Fixtures\CustomTestCase;
use Tests\Type\Fixtures\HelperTrait;

class PestConfigReaderTest extends TestCase
{
    private PestConfigReader $reader;

    private string $fixtureDir;

    protected function setUp(): void
    {
        $this->fixtureDir = realpath(__DIR__ . '/Fixtures/pestconfig');
        $discoverer = new PestFileDiscoverer([$this->fixtureDir]);
        $this->reader = new PestConfigReader([], $discoverer);
    }

    public function test_resolves_extend_binding_for_feature_directory(): void
    {
        $bindings = $this->reader->resolveBindings($this->fixtureDir . '/Feature/SomeTest.php');

        $this->assertContains(CustomTestCase::class, $bindings);
    }

    public function test_resolves_extend_binding_for_unit_directory(): void
    {
        $bindings = $this->reader->resolveBindings($this->fixtureDir . '/Unit/SomeTest.php');

        $this->assertContains(CustomTestCase::class, $bindings);
    }

    public function test_resolves_use_binding_for_helpers_subdirectory(): void
    {
        $bindings = $this->reader->resolveBindings($this->fixtureDir . '/Feature/Helpers/SomeTest.php');

        $this->assertContains(HelperTrait::class, $bindings);
    }

    public function test_accumulates_parent_and_subdirectory_bindings(): void
    {
        $bindings = $this->reader->resolveBindings($this->fixtureDir . '/Feature/Helpers/SomeTest.php');

        $this->assertContains(CustomTestCase::class, $bindings);
        $this->assertContains(HelperTrait::class, $bindings);
        $this->assertCount(2, $bindings);
    }

    public function test_returns_empty_for_unmatched_path(): void
    {
        $bindings = $this->reader->resolveBindings('/some/other/path/Test.php');

        $this->assertSame([], $bindings);
    }
}
