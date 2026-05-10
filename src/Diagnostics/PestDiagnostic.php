<?php

declare(strict_types=1);

namespace PestStan\Diagnostics;

final readonly class PestDiagnostic
{
    public function __construct(
        public string $kind,
        public string $identifier,
        public string $message,
        public ?string $tip = null,
        public ?int $line = null,
        public ?string $matcher = null,
        public ?string $valueType = null,
        public ?string $requirement = null,
        public ?string $lifecycleHook = null,
    ) {}
}
