<?php

declare(strict_types=1);

namespace PestStan\Analysis\Expectation;

final class ExpectationMatcherRegistry
{
    public const REQUIREMENT_STRING = 'string';

    public const REQUIREMENT_ITERABLE = 'iterable';

    public const REQUIREMENT_COUNTABLE_OR_ITERABLE = 'countable_or_iterable';

    public const TYPE_STRING = 'string';

    public const TYPE_INT = 'int';

    public const TYPE_FLOAT = 'float';

    public const TYPE_BOOL = 'bool';

    public const TYPE_TRUE = 'true';

    public const TYPE_FALSE = 'false';

    public const TYPE_NULL = 'null';

    public const TYPE_ARRAY = 'array';

    public const TYPE_LIST = 'list';

    public const TYPE_OBJECT = 'object';

    public const TYPE_CALLABLE = 'callable';

    public const TYPE_ITERABLE = 'iterable';

    public const TYPE_NUMERIC = 'numeric';

    public const TYPE_SCALAR = 'scalar';

    public const TYPE_INSTANCE_OF = 'instance_of';

    /** @var array<string, list<string>> */
    private const VALUE_REQUIREMENTS = [
        self::REQUIREMENT_STRING => [
            'json',
            'toStartWith',
            'toEndWith',
            'toBeJson',
            'toBeUppercase',
            'toBeLowercase',
            'toBeAlphaNumeric',
            'toBeAlpha',
            'toBeDigits',
            'toBeSnakeCase',
            'toBeKebabCase',
            'toBeCamelCase',
            'toBeStudlyCase',
            'toBeUuid',
            'toBeUrl',
            'toBeSlug',
            'toMatch',
            'toBeDirectory',
            'toBeFile',
            'toBeReadableFile',
            'toBeWritableFile',
            'toBeReadableDirectory',
            'toBeWritableDirectory',
        ],
        self::REQUIREMENT_ITERABLE => [
            'each',
            'sequence',
            'toContainEqual',
            'toContainOnlyInstancesOf',
        ],
        self::REQUIREMENT_COUNTABLE_OR_ITERABLE => [
            'toHaveCount',
            'toHaveSameSize',
        ],
    ];

    /** @var array<string, string> */
    private const TYPE_ASSERTIONS = [
        'toBeString' => self::TYPE_STRING,
        'toBeInt' => self::TYPE_INT,
        'toBeFloat' => self::TYPE_FLOAT,
        'toBeBool' => self::TYPE_BOOL,
        'toBeTrue' => self::TYPE_TRUE,
        'toBeFalse' => self::TYPE_FALSE,
        'toBeNull' => self::TYPE_NULL,
        'toBeArray' => self::TYPE_ARRAY,
        'toBeList' => self::TYPE_LIST,
        'toBeObject' => self::TYPE_OBJECT,
        'toBeCallable' => self::TYPE_CALLABLE,
        'toBeIterable' => self::TYPE_ITERABLE,
        'toBeNumeric' => self::TYPE_NUMERIC,
        'toBeScalar' => self::TYPE_SCALAR,
        'toBeInstanceOf' => self::TYPE_INSTANCE_OF,
    ];

    public function requirementFor(string $methodName): ?string
    {
        foreach (self::VALUE_REQUIREMENTS as $requirement => $methods) {
            if (in_array($methodName, $methods, true)) {
                return $requirement;
            }
        }

        return null;
    }

    public function impossibleOnType(string $methodName): ?string
    {
        return self::TYPE_ASSERTIONS[$methodName] ?? null;
    }

    public function redundantOnType(string $methodName): ?string
    {
        return self::TYPE_ASSERTIONS[$methodName] ?? null;
    }
}
