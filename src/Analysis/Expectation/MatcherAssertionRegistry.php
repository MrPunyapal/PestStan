<?php

declare(strict_types=1);

namespace PestStan\Analysis\Expectation;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Accessory\AccessoryArrayListType;
use PHPStan\Type\Accessory\AccessoryNumericStringType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\ResourceType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;

final class MatcherAssertionRegistry
{
    public const STRING = 'string';

    public const INT = 'int';

    public const FLOAT = 'float';

    public const BOOL = 'bool';

    public const TRUE = 'true';

    public const FALSE = 'false';

    public const NULL = 'null';

    public const ARRAY = 'array';

    public const LIST = 'list';

    public const OBJECT = 'object';

    public const CALLABLE = 'callable';

    public const ITERABLE = 'iterable';

    public const NUMERIC = 'numeric';

    public const SCALAR = 'scalar';

    public const INSTANCE_OF = 'instance_of';

    /** @var array<string, string> */
    private const METHOD_ASSERTIONS = [
        'toBeString' => self::STRING,
        'toBeInt' => self::INT,
        'toBeFloat' => self::FLOAT,
        'toBeBool' => self::BOOL,
        'toBeTrue' => self::TRUE,
        'toBeFalse' => self::FALSE,
        'toBeNull' => self::NULL,
        'toBeArray' => self::ARRAY,
        'toBeList' => self::LIST,
        'toBeObject' => self::OBJECT,
        'toBeCallable' => self::CALLABLE,
        'toBeIterable' => self::ITERABLE,
        'toBeNumeric' => self::NUMERIC,
        'toBeScalar' => self::SCALAR,
        'toBeInstanceOf' => self::INSTANCE_OF,
        'toBeResource' => 'resource',
    ];

    /** @var array<string, Type> */
    private array $staticAssertedTypeCache = [];

    public function assertionFor(string $methodName): ?string
    {
        return self::METHOD_ASSERTIONS[$methodName] ?? null;
    }

    public function assertedTypeFor(string $methodName, MethodCall $methodCall, Scope $scope): ?Type
    {
        $assertion = $this->assertionFor($methodName);
        if ($assertion === null) {
            return null;
        }

        if ($assertion !== self::INSTANCE_OF && isset($this->staticAssertedTypeCache[$assertion])) {
            return $this->staticAssertedTypeCache[$assertion];
        }

        $assertedType = match ($assertion) {
            self::STRING => new StringType,
            self::INT => new IntegerType,
            self::FLOAT => new FloatType,
            self::BOOL => new BooleanType,
            self::TRUE => new ConstantBooleanType(true),
            self::FALSE => new ConstantBooleanType(false),
            self::NULL => new NullType,
            self::OBJECT => new ObjectWithoutClassType,
            self::CALLABLE => new CallableType,
            'resource' => new ResourceType,
            self::ARRAY => new ArrayType(
                new UnionType([new IntegerType, new StringType]),
                new MixedType,
            ),
            self::LIST => TypeCombinator::intersect(
                new ArrayType(new IntegerType, new MixedType),
                new AccessoryArrayListType,
            ),
            self::ITERABLE => new IterableType(new MixedType, new MixedType),
            self::NUMERIC => new UnionType([
                new FloatType,
                new IntegerType,
                new IntersectionType([
                    new StringType,
                    new AccessoryNumericStringType,
                ]),
            ]),
            self::SCALAR => new UnionType([
                new BooleanType,
                new FloatType,
                new IntegerType,
                new StringType,
            ]),
            self::INSTANCE_OF => $this->resolveToBeInstanceOf($methodCall, $scope),
            default => null,
        };

        if (! $assertedType instanceof Type || $assertion === self::INSTANCE_OF) {
            return $assertedType;
        }

        $this->staticAssertedTypeCache[$assertion] = $assertedType;

        return $assertedType;
    }

    private function resolveToBeInstanceOf(MethodCall $methodCall, Scope $scope): Type
    {
        $args = $methodCall->getArgs();

        if ($args === []) {
            return new ObjectWithoutClassType;
        }

        $classType = $scope->getType($args[0]->value);
        $classNames = $classType->getConstantStrings();

        if ($classNames !== []) {
            $objectTypes = array_map(
                static fn ($name): ObjectType => new ObjectType($name->getValue()),
                $classNames
            );

            return TypeCombinator::union(...$objectTypes);
        }

        return new ObjectWithoutClassType;
    }
}
