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
use PHPUnit\Framework\TestCase;

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

    /** @param class-string $testCaseClass */
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly string $testCaseClass = TestCase::class,
    ) {}

    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if (! $classReflection->is(TestCall::class)) {
            return false;
        }

        if ($classReflection->hasNativeMethod($methodName)) {
            return false;
        }

        foreach ($this->mixinClasses() as $mixinClass) {
            if ($this->hasPublicMixinMethod($mixinClass, $methodName)) {
                return true;
            }
        }

        return false;
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        foreach ($this->mixinClasses() as $mixinClass) {
            if ($this->hasPublicMixinMethod($mixinClass, $methodName)) {
                return $this->reflectionProvider->getClass($mixinClass)->getNativeMethod($methodName);
            }
        }

        throw new LogicException(sprintf('Method %s not found on any TestCall mixin class.', $methodName));
    }

    /** @return list<class-string> */
    private function mixinClasses(): array
    {
        if ($this->testCaseClass === TestCase::class) {
            return self::MIXIN_CLASSES;
        }

        return [...self::MIXIN_CLASSES, $this->testCaseClass];
    }

    private function hasPublicMixinMethod(string $mixinClass, string $methodName): bool
    {
        if (! $this->reflectionProvider->hasClass($mixinClass)) {
            return false;
        }

        $mixinReflection = $this->reflectionProvider->getClass($mixinClass);
        if (! $mixinReflection->hasNativeMethod($methodName)) {
            return false;
        }

        return $mixinReflection->getNativeMethod($methodName)->isPublic();
    }
}
