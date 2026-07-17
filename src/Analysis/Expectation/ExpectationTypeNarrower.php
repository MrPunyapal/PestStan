<?php

declare(strict_types=1);

namespace PestStan\Analysis\Expectation;

use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;

final class ExpectationTypeNarrower
{
    public function hasOverlap(Type $incomingValueType, Type $expectedValueType): bool
    {
        if ($incomingValueType instanceof UnionType) {
            foreach ($incomingValueType->getTypes() as $innerType) {
                if ($this->hasOverlap($innerType, $expectedValueType)) {
                    return true;
                }
            }

            return false;
        }

        if ($incomingValueType->isObject()->yes() && $expectedValueType->isObject()->yes()) {
            $expectedIsSuperType = $expectedValueType->isSuperTypeOf($incomingValueType);
            $incomingIsSuperType = $incomingValueType->isSuperTypeOf($expectedValueType);

            if ($expectedIsSuperType->yes() || $incomingIsSuperType->yes()) {
                return true;
            }

            if (
                $expectedIsSuperType->no()
                && $incomingIsSuperType->no()
                && $incomingValueType->getObjectClassNames() !== []
                && $expectedValueType->getObjectClassNames() !== []
            ) {
                return false;
            }
        }

        return ! TypeCombinator::intersect($incomingValueType, $expectedValueType) instanceof NeverType;
    }

    public function narrow(Type $incomingValueType, Type $expectedValueType): Type
    {
        if ($expectedValueType->isSuperTypeOf($incomingValueType)->yes()) {
            return $incomingValueType;
        }

        if ($incomingValueType instanceof UnionType) {
            $narrowedTypes = [];

            foreach ($incomingValueType->getTypes() as $innerType) {
                if (! $this->hasOverlap($innerType, $expectedValueType)) {
                    continue;
                }

                $narrowedTypes[] = $expectedValueType->isSuperTypeOf($innerType)->yes()
                    ? $innerType
                    : $this->narrowSingleType($innerType, $expectedValueType);
            }

            if ($narrowedTypes === []) {
                return $expectedValueType;
            }

            return TypeCombinator::union(...$narrowedTypes);
        }

        return $this->narrowSingleType($incomingValueType, $expectedValueType);
    }

    private function narrowSingleType(Type $incomingValueType, Type $expectedValueType): Type
    {
        $intersection = TypeCombinator::intersect($incomingValueType, $expectedValueType);

        if ($intersection instanceof NeverType) {
            return $expectedValueType;
        }

        return $intersection;
    }
}
