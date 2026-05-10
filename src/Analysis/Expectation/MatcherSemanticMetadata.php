<?php

declare(strict_types=1);

namespace PestStan\Analysis\Expectation;

final readonly class MatcherSemanticMetadata
{
    /**
     * @param  list<string>  $categories
     */
    public function __construct(
        public string $methodName,
        public ?string $requirement,
        public ?string $assertion,
        public array $categories,
    ) {}
}
