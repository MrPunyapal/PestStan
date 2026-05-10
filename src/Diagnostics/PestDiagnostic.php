<?php

declare(strict_types=1);

namespace PestStan\Diagnostics;

use JsonSerializable;

final readonly class PestDiagnostic implements JsonSerializable
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

    /**
     * @return array<string, bool|int|string|null>
     */
    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'identifier' => PestDiagnosticIdentifiers::canonicalize($this->identifier),
            'severity' => $this->severity,
            'fixable' => $this->fixable,
            'message' => $this->message,
            'tip' => $this->tip,
            'line' => $this->line,
            'semanticCategory' => $this->semanticCategory,
            'confidenceLevel' => $this->confidenceLevel,
            'fixStrategy' => $this->fixStrategy,
            'fixRule' => $this->fixRule,
            'semanticCode' => $this->semanticCode,
            'matcherCategory' => $this->matcherCategory,
            'suggestedFix' => $this->suggestedFix,
            'relatedMatcher' => $this->relatedMatcher,
            'expectedType' => $this->expectedType,
            'actualType' => $this->actualType,
            'matcher' => $this->matcher,
            'valueType' => $this->valueType,
            'requirement' => $this->requirement,
            'lifecycleHook' => $this->lifecycleHook,
        ];
    }

    /**
     * @return array<string, bool|int|string|null>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
