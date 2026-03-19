<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\MixedType;
use PHPUnit\Framework\TestCase;

/**
 * Allows dynamic property access on TestCase subclasses used with Pest.
 * Pest supports setting/getting arbitrary properties on $this within test closures.
 */
final class TestCasePropertiesExtension implements PropertiesClassReflectionExtension
{
    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        return $classReflection->is(TestCase::class);
    }

    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        return new PestTestCaseProperty($classReflection, new MixedType);
    }
}
