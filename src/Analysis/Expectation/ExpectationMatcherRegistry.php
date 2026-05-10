<?php

declare(strict_types=1);

namespace PestStan\Analysis\Expectation;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Type;

final class ExpectationMatcherRegistry
{
    public const REQUIREMENT_STRING = MatcherRequirementRegistry::STRING;

    public const REQUIREMENT_ITERABLE = MatcherRequirementRegistry::ITERABLE;

    public const REQUIREMENT_COUNTABLE_OR_ITERABLE = MatcherRequirementRegistry::COUNTABLE_OR_ITERABLE;

    public const TYPE_STRING = MatcherAssertionRegistry::STRING;

    public const TYPE_INT = MatcherAssertionRegistry::INT;

    public const TYPE_FLOAT = MatcherAssertionRegistry::FLOAT;

    public const TYPE_BOOL = MatcherAssertionRegistry::BOOL;

    public const TYPE_TRUE = MatcherAssertionRegistry::TRUE;

    public const TYPE_FALSE = MatcherAssertionRegistry::FALSE;

    public const TYPE_NULL = MatcherAssertionRegistry::NULL;

    public const TYPE_ARRAY = MatcherAssertionRegistry::ARRAY;

    public const TYPE_LIST = MatcherAssertionRegistry::LIST;

    public const TYPE_OBJECT = MatcherAssertionRegistry::OBJECT;

    public const TYPE_CALLABLE = MatcherAssertionRegistry::CALLABLE;

    public const TYPE_ITERABLE = MatcherAssertionRegistry::ITERABLE;

    public const TYPE_NUMERIC = MatcherAssertionRegistry::NUMERIC;

    public const TYPE_SCALAR = MatcherAssertionRegistry::SCALAR;

    public const TYPE_INSTANCE_OF = MatcherAssertionRegistry::INSTANCE_OF;

    private readonly MatcherRequirementRegistry $requirementRegistry;

    private readonly MatcherAssertionRegistry $assertionRegistry;

    private readonly MatcherCategoryRegistry $categoryRegistry;

    /** @var array<string, MatcherSemanticMetadata|null> */
    private array $metadataCache = [];

    public function __construct(
        ?MatcherRequirementRegistry $requirementRegistry = null,
        ?MatcherAssertionRegistry $assertionRegistry = null,
        ?MatcherCategoryRegistry $categoryRegistry = null,
    ) {
        $this->requirementRegistry = $requirementRegistry ?? new MatcherRequirementRegistry;
        $this->assertionRegistry = $assertionRegistry ?? new MatcherAssertionRegistry;
        $this->categoryRegistry = $categoryRegistry ?? new MatcherCategoryRegistry;
    }

    public function requirementFor(string $methodName): ?string
    {
        return $this->metadataFor($methodName)?->requirement;
    }

    /**
     * Compatibility wrapper retained for older callers that still ask for
     * impossible matcher semantics through the pre-semantic registry API.
     */
    public function impossibleOnType(string $methodName): ?string
    {
        return $this->metadataFor($methodName)?->assertion;
    }

    /**
     * Compatibility wrapper retained for older callers that still ask for
     * redundant matcher semantics through the pre-semantic registry API.
     */
    public function redundantOnType(string $methodName): ?string
    {
        return $this->metadataFor($methodName)?->assertion;
    }

    public function assertionFor(string $methodName): ?string
    {
        return $this->metadataFor($methodName)?->assertion;
    }

    public function assertedTypeFor(string $methodName, MethodCall $methodCall, Scope $scope): ?Type
    {
        return $this->assertionRegistry->assertedTypeFor($methodName, $methodCall, $scope);
    }

    public function metadataFor(string $methodName): ?MatcherSemanticMetadata
    {
        if (array_key_exists($methodName, $this->metadataCache)) {
            return $this->metadataCache[$methodName];
        }

        $requirement = $this->requirementRegistry->requirementFor($methodName);
        $assertion = $this->assertionRegistry->assertionFor($methodName);
        $categories = $this->categoryRegistry->categoriesFor($methodName);

        if ($requirement === null && $assertion === null && $categories === []) {
            $this->metadataCache[$methodName] = null;

            return null;
        }

        $this->metadataCache[$methodName] = new MatcherSemanticMetadata(
            $methodName,
            $requirement,
            $assertion,
            $categories,
        );

        return $this->metadataCache[$methodName];
    }

    public function primaryCategoryFor(string $methodName): ?string
    {
        return $this->categoryRegistry->primaryCategoryFor($methodName);
    }
}
