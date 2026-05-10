<?php

declare(strict_types=1);

namespace PestStan\Diagnostics;

final readonly class PestDiagnostic
{
    public function __construct(
        public string $kind,
        public string $identifier,
        public string $severity,
        public bool $fixable,
        public string $message,
        public ?string $tip = null,
        public ?int $line = null,
        public ?string $semanticCategory = null,
        public ?string $confidenceLevel = null,
        public ?string $fixStrategy = null,
        public ?string $fixRule = null,
        public ?string $semanticCode = null,
        public ?string $matcherCategory = null,
        public ?string $suggestedFix = null,
        public ?string $relatedMatcher = null,
        public ?string $expectedType = null,
        public ?string $actualType = null,
        public ?string $matcher = null,
        public ?string $valueType = null,
        public ?string $requirement = null,
        public ?string $lifecycleHook = null,
    ) {}
}
