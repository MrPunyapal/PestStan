<?php

declare(strict_types=1);

namespace PestStan\Diagnostics;

final class PestDiagnosticIdentifiers
{
    public const TEST_DUPLICATE_DESCRIPTION = 'pest.test.duplicateDescription';

    public const GROUP_INVALID_NAME = 'pest.group.invalidName';

    public const TEST_STATIC_CLOSURE = 'pest.test.staticClosure';

    public const REPEAT_INVALID_VALUE = 'pest.repeat.invalidValue';

    public const DESCRIBE_BEFORE_ALL_DISALLOWED = 'pest.describe.beforeAllDisallowed';

    public const DESCRIBE_AFTER_ALL_DISALLOWED = 'pest.describe.afterAllDisallowed';

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
}
