<?php

declare(strict_types=1);

namespace PestStan\Diagnostics;

final class PestDiagnosticIdentifiers
{
    public const EXPECTATION_REQUIRES_STRING = 'pest.expectation.requiresString';

    public const EXPECTATION_REQUIRES_ITERABLE = 'pest.expectation.requiresIterable';

    public const EXPECTATION_REQUIRES_COUNTABLE_OR_ITERABLE = 'pest.expectation.requiresCountableOrIterable';

    public const EXPECTATION_IMPOSSIBLE = 'pest.expectation.impossible';

    public const EXPECTATION_REDUNDANT = 'pest.expectation.redundant';

    public const LIFECYCLE_BEFORE_ALL_THIS_USAGE = 'pest.lifecycle.beforeAllThisUsage';

    public const LIFECYCLE_AFTER_ALL_THIS_USAGE = 'pest.lifecycle.afterAllThisUsage';
}
