<?php

declare(strict_types=1);

namespace PestStan\Analysis\Expectation;

use PHPStan\Type\Type;

final readonly class ExpectationChainState
{
    public function __construct(
        public Type $originalValueType,
        public Type $currentValueType,
        public bool $broken,
        public ?ExpectationAssertionResult $stepResult = null,
    ) {}

    public static function root(Type $valueType): self
    {
        return new self($valueType, $valueType, false);
    }

    public function next(ExpectationAssertionResult $stepResult): self
    {
        return new self(
            $this->originalValueType,
            $stepResult->resultingValueType,
            $this->broken || $stepResult->breaksChain(),
            $stepResult,
        );
    }
}
