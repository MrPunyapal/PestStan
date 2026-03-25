<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use Pest\Expectation;
use Pest\Expectations\HigherOrderExpectation;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\MixedType;

final class ExpectationPropertiesExtension implements PropertiesClassReflectionExtension
{
    /** @var list<string> */
    private const KNOWN_EXPECTATION_PROPERTIES = ['not', 'each', 'classes', 'traits', 'interfaces', 'enums', 'value'];

    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        if ($classReflection->is(Expectation::class)) {
            return ! in_array($propertyName, self::KNOWN_EXPECTATION_PROPERTIES, true)
                && ! $classReflection->hasNativeProperty($propertyName);
        }

        if ($classReflection->is(HigherOrderExpectation::class)) {
            return ! $classReflection->hasNativeProperty($propertyName);
        }

        return false;
    }

    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        return new PestTestCaseProperty($classReflection, new MixedType);
    }
}
