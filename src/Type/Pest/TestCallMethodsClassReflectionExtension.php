<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use LogicException;
use Pest\Concerns\Testable;
use Pest\PendingCalls\TestCall;
use Pest\Support\HigherOrderCallables;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\ReflectionProvider;

/**
 * Resolves methods on TestCall that originate from its @mixin types.
 *
 * PHPStan's native @mixin resolution does not support union types, so
 * `@mixin HigherOrderCallables|TestCase|Testable` on TestCall is silently
 * ignored, causing method.notFound for methods like preset().
 *
 * This extension resolves methods from the Pest-specific mixin types in
 * declaration order, returning the real MethodReflection with its correct
 * return type so that downstream chains (e.g. preset()->php()) resolve fully.
 */
final class TestCallMethodsClassReflectionExtension implements MethodsClassReflectionExtension
{
    /** @var list<class-string> */
    private const MIXIN_CLASSES = [
        Testable::class,
        HigherOrderCallables::class,
    ];

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
    ) {}

    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if (! $classReflection->is(TestCall::class)) {
            return false;
        }

        if ($classReflection->hasNativeMethod($methodName)) {
            return false;
        }

        foreach (self::MIXIN_CLASSES as $mixinClass) {
            if (
                $this->reflectionProvider->hasClass($mixinClass)
                && $this->reflectionProvider->getClass($mixinClass)->hasNativeMethod($methodName)
            ) {
                return true;
            }
        }

        return false;
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        foreach (self::MIXIN_CLASSES as $mixinClass) {
            if (! $this->reflectionProvider->hasClass($mixinClass)) {
                continue;
            }

            $mixinReflection = $this->reflectionProvider->getClass($mixinClass);

            if ($mixinReflection->hasNativeMethod($methodName)) {
                return $mixinReflection->getNativeMethod($methodName);
            }
        }

        throw new LogicException(sprintf('Method %s not found on any TestCall mixin class.', $methodName));
    }
}
