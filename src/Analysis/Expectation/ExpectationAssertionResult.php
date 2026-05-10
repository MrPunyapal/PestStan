<?php

declare(strict_types=1);

namespace PestStan\Analysis\Expectation;

use PHPStan\Type\Type;

final readonly class ExpectationAssertionResult
{
    public const PASSTHROUGH = 'passthrough';

    public const ASSERTED = 'asserted';

    public const REDUNDANT = 'redundant';

    public const IMPOSSIBLE = 'impossible';

    public const INVALID_REQUIREMENT = 'invalid_requirement';

    public const SKIPPED_BROKEN_CHAIN = 'skipped_broken_chain';

    public function __construct(
        public string $methodName,
        public ?MatcherSemanticMetadata $metadata,
        public string $status,
        public Type $incomingValueType,
        public Type $resultingValueType,
        public ?Type $expectedValueType = null,
    ) {}

    public function breaksChain(): bool
    {
        return in_array($this->status, [self::IMPOSSIBLE, self::INVALID_REQUIREMENT, self::SKIPPED_BROKEN_CHAIN], true);
    }

    public function isInvalidRequirement(): bool
    {
        return $this->status === self::INVALID_REQUIREMENT;
    }

    public function isImpossible(): bool
    {
        return $this->status === self::IMPOSSIBLE;
    }

    public function isRedundant(): bool
    {
        return $this->status === self::REDUNDANT;
    }
}
