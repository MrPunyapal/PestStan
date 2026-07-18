<?php

declare(strict_types=1);

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

/**
 * Per-node WeakMap memoisation for the resolver's chain-state cache.
 *
 * The resolver is instantiated once by PHPStan's container and shared across
 * every analysed file. Its cache must therefore key on the node identity, not
 * on spl_object_id(): PHP recycles object ids as soon as a node is freed, so an
 * integer-keyed cache would leak a previous file's chain state onto an
 * unrelated node in a later file that happens to reuse the id.
 */
beforeEach(function (): void {
    PHPStanTestCase::getContainer();
});

test('it resolves each node from its own scope', function (): void {
    $resolver = new ExpectationChainStateResolver;
    $intCall = expectationEachCall();
    $stringCall = expectationEachCall();
    $intState = $resolver->resolve($intCall, scopeReturningExpectationOf(new IntegerType, fn (string $class): Scope => $this->createMock($class)));
    $stringState = $resolver->resolve($stringCall, scopeReturningExpectationOf(new StringType, fn (string $class): Scope => $this->createMock($class)));
    expect($intState)->not->toBeNull()
        ->and($stringState)->not->toBeNull()
        ->and($intState->originalValueType->describe(VerbosityLevel::typeOnly()))->toBe('int')
        ->and($stringState->originalValueType->describe(VerbosityLevel::typeOnly()))->toBe('string');
});

test('it evicts cache entries when their node is freed', function (): void {
    $resolver = new ExpectationChainStateResolver;
    $cache = cacheOf($resolver);
    expect($cache)->toBeEmpty();
    $call = expectationEachCall();
    $resolver->resolve($call, scopeReturningExpectationOf(new IntegerType, fn (string $class): Scope => $this->createMock($class)));
    expect($cache)->toHaveCount(1, 'The resolved node should be memoised.');
    unset($call);
    expect($cache)->toHaveCount(
        0,
        'A freed node must not leave a dangling cache entry keyed by a recyclable id.',
    );
});

// --- Helper functions ---

function expectationEachCall(): MethodCall
{
    return new MethodCall(new Variable('expectation'), new Identifier('each'));
}

function scopeReturningExpectationOf(Type $valueType, callable $mockFactory): Scope
{
    $expectationType = new GenericObjectType(Expectation::class, [$valueType]);
    $scope = $mockFactory(Scope::class);
    $scope->method('getType')->willReturn($expectationType);

    return $scope;
}

/**
 * @return WeakMap<MethodCall, mixed>
 */
function cacheOf(ExpectationChainStateResolver $resolver): WeakMap
{
    $property = new ReflectionProperty($resolver, 'stateCache');
    $cache = $property->getValue($resolver);
    expect($cache)->toBeInstanceOf(WeakMap::class);

    return $cache;
}
