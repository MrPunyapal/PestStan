<?php

declare(strict_types=1);

namespace PestStan\Diagnostics;

final class PestDiagnosticIdentifiers
{
    public const TEST_DUPLICATE_DESCRIPTION = 'pest.test.duplicateDescription';

    public const GROUP_INVALID_NAME = 'pest.group.invalidName';

    public const TEST_STATIC_CLOSURE = 'pest.test.staticClosure';

    public const REPEAT_INVALID_VALUE = 'pest.execution.invalidRepeatValue';

    public const DESCRIBE_BEFORE_ALL_DISALLOWED = 'pest.lifecycle.beforeAllDisallowed';

    public const DESCRIBE_AFTER_ALL_DISALLOWED = 'pest.lifecycle.afterAllDisallowed';

    public const COVERS_FUNCTION_NOT_FOUND = 'pest.covers.functionNotFound';

    public const COVERS_CLASS_NOT_FOUND = 'pest.covers.classNotFound';

    public const TEST_EMPTY_CLOSURE = 'pest.test.emptyClosure';

    public const THROWS_CLASS_NOT_FOUND = 'pest.throws.classNotFound';

    public const THROWS_INVALID_EXCEPTION = 'pest.throws.invalidException';

    public const DESCRIBE_WITHOUT_TESTS = 'pest.describe.withoutTests';

    public const EXPECTATION_REQUIRES_STRING = 'pest.expectation.requiresString';

    public const EXPECTATION_REQUIRES_ITERABLE = 'pest.expectation.requiresIterable';

    public const EXPECTATION_REQUIRES_COUNTABLE_OR_ITERABLE = 'pest.expectation.requiresCountableOrIterable';

    public const EXPECTATION_IMPOSSIBLE = 'pest.expectation.impossible';

    public const EXPECTATION_REDUNDANT = 'pest.expectation.redundant';

    public const LIFECYCLE_BEFORE_ALL_THIS_USAGE = 'pest.lifecycle.beforeAllThisUsage';

    public const LIFECYCLE_AFTER_ALL_THIS_USAGE = 'pest.lifecycle.afterAllThisUsage';

    public const CONFIG_REDUNDANT_LOCAL_USE = 'pest.config.redundantLocalUse';

    /** @var array<string, string> */
    private const ALIAS_TO_CANONICAL = [
        'pest.duplicateTestDescription' => self::TEST_DUPLICATE_DESCRIPTION,
        'pest.invalidGroupName' => self::GROUP_INVALID_NAME,
        'pest.staticTestClosure' => self::TEST_STATIC_CLOSURE,
        'pest.repeatInvalidValue' => self::REPEAT_INVALID_VALUE,
        'pest.repeat.invalidValue' => self::REPEAT_INVALID_VALUE,
        'pest.beforeAllInDescribe' => self::DESCRIBE_BEFORE_ALL_DISALLOWED,
        'pest.afterAllInDescribe' => self::DESCRIBE_AFTER_ALL_DISALLOWED,
        'pest.describe.beforeAllDisallowed' => self::DESCRIBE_BEFORE_ALL_DISALLOWED,
        'pest.describe.afterAllDisallowed' => self::DESCRIBE_AFTER_ALL_DISALLOWED,
        'pest.coversFunctionNotFound' => self::COVERS_FUNCTION_NOT_FOUND,
        'pest.coversClassNotFound' => self::COVERS_CLASS_NOT_FOUND,
        'pest.emptyTestClosure' => self::TEST_EMPTY_CLOSURE,
        'pest.throwsClassNotFound' => self::THROWS_CLASS_NOT_FOUND,
        'pest.invalidThrowsException' => self::THROWS_INVALID_EXCEPTION,
        'pest.describeWithoutTests' => self::DESCRIBE_WITHOUT_TESTS,
        'pest.expectationRequiresString' => self::EXPECTATION_REQUIRES_STRING,
        'pest.expectationRequiresIterable' => self::EXPECTATION_REQUIRES_ITERABLE,
        'pest.expectationRequiresCountableOrIterable' => self::EXPECTATION_REQUIRES_COUNTABLE_OR_ITERABLE,
        'pest.impossibleExpectation' => self::EXPECTATION_IMPOSSIBLE,
        'pest.redundantExpectation' => self::EXPECTATION_REDUNDANT,
        'pest.beforeAllThisUsage' => self::LIFECYCLE_BEFORE_ALL_THIS_USAGE,
        'pest.afterAllThisUsage' => self::LIFECYCLE_AFTER_ALL_THIS_USAGE,
    ];

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        static $all = null;

        if (is_array($all)) {
            /** @var list<string> $all */
            return $all;
        }

        /** @var list<string> $all */
        $all = [
            self::TEST_DUPLICATE_DESCRIPTION,
            self::GROUP_INVALID_NAME,
            self::TEST_STATIC_CLOSURE,
            self::REPEAT_INVALID_VALUE,
            self::DESCRIBE_BEFORE_ALL_DISALLOWED,
            self::DESCRIBE_AFTER_ALL_DISALLOWED,
            self::COVERS_FUNCTION_NOT_FOUND,
            self::COVERS_CLASS_NOT_FOUND,
            self::TEST_EMPTY_CLOSURE,
            self::THROWS_CLASS_NOT_FOUND,
            self::THROWS_INVALID_EXCEPTION,
            self::DESCRIBE_WITHOUT_TESTS,
            self::EXPECTATION_REQUIRES_STRING,
            self::EXPECTATION_REQUIRES_ITERABLE,
            self::EXPECTATION_REQUIRES_COUNTABLE_OR_ITERABLE,
            self::EXPECTATION_IMPOSSIBLE,
            self::EXPECTATION_REDUNDANT,
            self::LIFECYCLE_BEFORE_ALL_THIS_USAGE,
            self::LIFECYCLE_AFTER_ALL_THIS_USAGE,
            self::CONFIG_REDUNDANT_LOCAL_USE,
        ];

        return $all;
    }

    public static function canonicalize(string $identifier): string
    {
        return self::ALIAS_TO_CANONICAL[$identifier] ?? $identifier;
    }

    public static function isCanonical(string $identifier): bool
    {
        return in_array($identifier, self::all(), true);
    }

    /**
     * @return list<string>
     */
    public static function aliasesFor(string $identifier): array
    {
        $canonical = self::canonicalize($identifier);

        static $aliasesByCanonical = null;

        if (! is_array($aliasesByCanonical)) {
            /** @var array<string, list<string>> $aliasesByCanonical */
            $aliasesByCanonical = [];

            foreach (self::ALIAS_TO_CANONICAL as $alias => $resolvedIdentifier) {
                $aliasesByCanonical[$resolvedIdentifier] ??= [];
                $aliasesByCanonical[$resolvedIdentifier][] = $alias;
            }
        }

        /** @var list<string> $aliases */
        $aliases = $aliasesByCanonical[$canonical] ?? [];

        return $aliases;
    }
}
