<?php

declare(strict_types=1);

namespace PestStan\Analysis\Expectation;

use Countable;
use Pest\Expectation;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

final class ExpectationChainStateResolver
{
    private readonly ExpectationMatcherRegistry $matcherRegistry;

    private readonly ExpectationTypeNarrower $typeNarrower;

    /** @var array<int, ExpectationChainState|null> */
    private array $stateCache = [];

    public function __construct(
        ?ExpectationMatcherRegistry $matcherRegistry = null,
        ?ExpectationTypeNarrower $typeNarrower = null,
    ) {
        $this->matcherRegistry = $matcherRegistry ?? new ExpectationMatcherRegistry;
        $this->typeNarrower = $typeNarrower ?? new ExpectationTypeNarrower;
    }

    public function resolve(MethodCall $methodCall, Scope $scope): ?ExpectationChainState
    {
        $cacheKey = spl_object_id($methodCall);

        if (array_key_exists($cacheKey, $this->stateCache)) {
            return $this->stateCache[$cacheKey];
        }

        $this->stateCache[$cacheKey] = $this->resolveState($methodCall, $scope);

        return $this->stateCache[$cacheKey];
    }

    private function resolveState(MethodCall $methodCall, Scope $scope): ?ExpectationChainState
    {
        if (! $methodCall->name instanceof Identifier) {
            return null;
        }

        $previousState = $methodCall->var instanceof MethodCall
            ? $this->resolve($methodCall->var, $scope)
            : $this->resolveRootState($methodCall, $scope);

        if (! $previousState instanceof ExpectationChainState) {
            return null;
        }

        $stepResult = $this->resolveStepResult(
            $methodCall->name->name,
            $previousState,
            $methodCall,
            $scope,
        );

        return $previousState->next($stepResult);
    }

    private function resolveRootState(MethodCall $methodCall, Scope $scope): ?ExpectationChainState
    {
        $callerType = $scope->getType($methodCall->var);
        if (! (new ObjectType(Expectation::class))->isSuperTypeOf($callerType)->yes()) {
            return null;
        }

        return ExpectationChainState::root(
            $callerType->getTemplateType(Expectation::class, 'TValue')
        );
    }

    private function resolveStepResult(
        string $methodName,
        ExpectationChainState $previousState,
        MethodCall $methodCall,
        Scope $scope,
    ): ExpectationAssertionResult {
        $metadata = $this->matcherRegistry->metadataFor($methodName);
        $incomingValueType = $previousState->currentValueType;

        if ($previousState->broken) {
            return new ExpectationAssertionResult(
                $methodName,
                $metadata,
                ExpectationAssertionResult::SKIPPED_BROKEN_CHAIN,
                $incomingValueType,
                $incomingValueType,
            );
        }

        if (! $metadata instanceof MatcherSemanticMetadata) {
            return new ExpectationAssertionResult(
                $methodName,
                null,
                ExpectationAssertionResult::PASSTHROUGH,
                $incomingValueType,
                $this->resolveResultingValueType($methodCall, $scope, $incomingValueType),
            );
        }

        if ($metadata->requirement !== null && $this->violatesRequirement($incomingValueType, $metadata->requirement)) {
            return new ExpectationAssertionResult(
                $methodName,
                $metadata,
                ExpectationAssertionResult::INVALID_REQUIREMENT,
                $incomingValueType,
                $incomingValueType,
            );
        }

        if ($metadata->assertion === null) {
            return new ExpectationAssertionResult(
                $methodName,
                $metadata,
                ExpectationAssertionResult::PASSTHROUGH,
                $incomingValueType,
                $this->resolveResultingValueType($methodCall, $scope, $incomingValueType),
            );
        }

        $expectedValueType = $this->matcherRegistry->assertedTypeFor($methodName, $methodCall, $scope);
        if (! $expectedValueType instanceof Type) {
            return new ExpectationAssertionResult(
                $methodName,
                $metadata,
                ExpectationAssertionResult::PASSTHROUGH,
                $incomingValueType,
                $this->resolveResultingValueType($methodCall, $scope, $incomingValueType),
            );
        }

        if (! $this->typeNarrower->hasOverlap($incomingValueType, $expectedValueType)) {
            return new ExpectationAssertionResult(
                $methodName,
                $metadata,
                ExpectationAssertionResult::IMPOSSIBLE,
                $incomingValueType,
                $incomingValueType,
                $expectedValueType,
            );
        }

        $resultingValueType = $this->typeNarrower->narrow($incomingValueType, $expectedValueType);
        $status = $expectedValueType->isSuperTypeOf($incomingValueType)->yes()
            ? ExpectationAssertionResult::REDUNDANT
            : ExpectationAssertionResult::ASSERTED;

        return new ExpectationAssertionResult(
            $methodName,
            $metadata,
            $status,
            $incomingValueType,
            $resultingValueType,
            $expectedValueType,
        );
    }

    private function resolveResultingValueType(MethodCall $methodCall, Scope $scope, Type $fallbackType): Type
    {
        $methodType = $scope->getType($methodCall);
        if (! (new ObjectType(Expectation::class))->isSuperTypeOf($methodType)->yes()) {
            return $fallbackType;
        }

        return $methodType->getTemplateType(Expectation::class, 'TValue');
    }

    private function violatesRequirement(Type $valueType, string $requirement): bool
    {
        return match ($requirement) {
            MatcherRequirementRegistry::STRING => $valueType->isString()->no(),
            MatcherRequirementRegistry::ITERABLE => $valueType->isIterable()->no(),
            MatcherRequirementRegistry::COUNTABLE_OR_ITERABLE => $valueType->isIterable()->no()
                && (new ObjectType(Countable::class))->isSuperTypeOf($valueType)->no(),
            default => false,
        };
    }
}
