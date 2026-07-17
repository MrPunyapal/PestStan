<?php

declare(strict_types=1);

namespace Tests\Analysis;

use Pest\Expectation;
use PestStan\Analysis\Expectation\ExpectationChainStateResolver;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Testing\PHPStanTestCase;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use ReflectionProperty;
use WeakMap;

/**
 * Regression coverage for the resolver's per-node memoisation.
 *
 * The resolver is instantiated once by PHPStan's container and shared across
 * every analysed file. Its cache must therefore key on the node identity, not
 * on spl_object_id(): PHP recycles object ids as soon as a node is freed, so an
 * integer-keyed cache would leak a previous file's chain state onto an
 * unrelated node in a later file that happens to reuse the id.
 */
final class ExpectationChainStateResolverTest extends PHPStanTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Boot the PHPStan container so the reflection provider static accessor
        // is available to GenericObjectType / ObjectType during resolution.
        self::getContainer();
    }

    public function test_it_resolves_each_node_from_its_own_scope(): void
    {
        $resolver = new ExpectationChainStateResolver;
        $intCall = $this->expectationEachCall();
        $stringCall = $this->expectationEachCall();
        $intState = $resolver->resolve($intCall, $this->scopeReturningExpectationOf(new IntegerType));
        $stringState = $resolver->resolve($stringCall, $this->scopeReturningExpectationOf(new StringType));
        self::assertNotNull($intState);
        self::assertNotNull($stringState);
        self::assertSame('int', $intState->originalValueType->describe(VerbosityLevel::typeOnly()));
        self::assertSame('string', $stringState->originalValueType->describe(VerbosityLevel::typeOnly()));
    }

    public function test_it_evicts_cache_entries_when_their_node_is_freed(): void
    {
        $resolver = new ExpectationChainStateResolver;
        $cache = $this->cacheOf($resolver);
        self::assertCount(0, $cache);
        $call = $this->expectationEachCall();
        $resolver->resolve($call, $this->scopeReturningExpectationOf(new IntegerType));
        self::assertCount(1, $cache, 'The resolved node should be memoised.');
        // Freeing the node must release its cache entry, so a later node that
        // reuses the same spl_object_id cannot inherit a stale chain state.
        unset($call);
        self::assertCount(
            0,
            $cache,
            'A freed node must not leave a dangling cache entry keyed by a recyclable id.',
        );
    }

    private function expectationEachCall(): MethodCall
    {
        return new MethodCall(new Variable('expectation'), new Identifier('each'));
    }

    private function scopeReturningExpectationOf(Type $valueType): Scope
    {
        $expectationType = new GenericObjectType(Expectation::class, [$valueType]);
        $scope = $this->createMock(Scope::class);
        $scope->method('getType')->willReturn($expectationType);

        return $scope;
    }

    /**
     * @return WeakMap<MethodCall, mixed>
     */
    private function cacheOf(ExpectationChainStateResolver $resolver): WeakMap
    {
        $property = new ReflectionProperty($resolver, 'stateCache');
        $cache = $property->getValue($resolver);
        self::assertInstanceOf(WeakMap::class, $cache);

        return $cache;
    }
}
