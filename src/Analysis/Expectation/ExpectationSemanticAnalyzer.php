<?php

declare(strict_types=1);

namespace PestStan\Analysis\Expectation;

use Countable;
use Pest\Expectation;
use PestStan\Diagnostics\PestDiagnostic;
use PestStan\Diagnostics\PestDiagnostics;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

final class ExpectationSemanticAnalyzer
{
    private readonly ExpectationMatcherRegistry $matcherRegistry;

    public function __construct(?ExpectationMatcherRegistry $matcherRegistry = null)
    {
        $this->matcherRegistry = $matcherRegistry ?? new ExpectationMatcherRegistry;
    }

    public function analyzeInvalidMatcherType(MethodCall $methodCall, Scope $scope): ?PestDiagnostic
    {
        if ($this->hasInvalidPriorExpectationStep($methodCall, $scope)) {
            return null;
        }

        $context = $this->resolveContext($methodCall, $scope);
        if ($context === null || $context['valueType'] instanceof MixedType) {
            return null;
        }

        $requirement = $this->matcherRegistry->requirementFor($context['methodName']);
        if ($requirement === null || ! $this->violatesRequirement($context['valueType'], $requirement)) {
            return null;
        }

        return PestDiagnostics::invalidMatcherType(
            $context['methodName'],
            $context['typeDescription'],
            $requirement,
        );
    }

    public function analyzeImpossibleExpectation(MethodCall $methodCall, Scope $scope): ?PestDiagnostic
    {
        if ($this->hasInvalidPriorExpectationStep($methodCall, $scope)) {
            return null;
        }

        $context = $this->resolveContext($methodCall, $scope);
        if ($context === null || $context['valueType'] instanceof MixedType) {
            return null;
        }

        $typeAssertion = $this->matcherRegistry->impossibleOnType($context['methodName']);
        if ($typeAssertion === null || ! $this->isImpossible($typeAssertion, $context['valueType'], $methodCall, $scope)) {
            return null;
        }

        return PestDiagnostics::impossibleExpectation($context['methodName'], $context['typeDescription']);
    }

    public function analyzeRedundantExpectation(MethodCall $methodCall, Scope $scope): ?PestDiagnostic
    {
        if ($this->hasInvalidPriorExpectationStep($methodCall, $scope)) {
            return null;
        }

        $context = $this->resolveContext($methodCall, $scope);
        if ($context === null || $context['valueType'] instanceof MixedType) {
            return null;
        }

        $typeAssertion = $this->matcherRegistry->redundantOnType($context['methodName']);
        if ($typeAssertion === null || ! $this->isRedundant($typeAssertion, $context['valueType'], $methodCall, $scope)) {
            return null;
        }

        return PestDiagnostics::redundantExpectation($context['methodName'], $context['typeDescription']);
    }

    private function hasInvalidPriorExpectationStep(MethodCall $methodCall, Scope $scope): bool
    {
        foreach ($this->priorMethodCalls($methodCall) as $priorMethodCall) {
            $context = $this->resolveContext($priorMethodCall, $scope);
            if ($context === null) {
                continue;
            }

            if ($context['valueType'] instanceof MixedType) {
                continue;
            }

            $requirement = $this->matcherRegistry->requirementFor($context['methodName']);
            if ($requirement !== null && $this->violatesRequirement($context['valueType'], $requirement)) {
                return true;
            }

            $typeAssertion = $this->matcherRegistry->impossibleOnType($context['methodName']);
            if ($typeAssertion !== null && $this->isImpossible($typeAssertion, $context['valueType'], $priorMethodCall, $scope)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{methodName: string, valueType: Type, typeDescription: string}|null
     */
    private function resolveContext(MethodCall $methodCall, Scope $scope): ?array
    {
        if (! $methodCall->name instanceof Identifier) {
            return null;
        }

        $callerType = $scope->getType($methodCall->var);
        if (! (new ObjectType(Expectation::class))->isSuperTypeOf($callerType)->yes()) {
            return null;
        }

        $valueType = $callerType->getTemplateType(Expectation::class, 'TValue');

        return [
            'methodName' => $methodCall->name->name,
            'valueType' => $valueType,
            'typeDescription' => $valueType->describe(VerbosityLevel::typeOnly()),
        ];
    }

    /**
     * @return list<MethodCall>
     */
    private function priorMethodCalls(MethodCall $methodCall): array
    {
        $priorMethodCalls = [];
        $expr = $methodCall->var;

        while ($expr instanceof MethodCall) {
            $priorMethodCalls[] = $expr;
            $expr = $expr->var;
        }

        return array_reverse($priorMethodCalls);
    }

    private function violatesRequirement(Type $valueType, string $requirement): bool
    {
        return match ($requirement) {
            ExpectationMatcherRegistry::REQUIREMENT_STRING => $valueType->isString()->no(),
            ExpectationMatcherRegistry::REQUIREMENT_ITERABLE => $valueType->isIterable()->no(),
            ExpectationMatcherRegistry::REQUIREMENT_COUNTABLE_OR_ITERABLE => $valueType->isIterable()->no()
                && (new ObjectType(Countable::class))->isSuperTypeOf($valueType)->no(),
            default => false,
        };
    }

    private function isImpossible(string $typeAssertion, Type $valueType, MethodCall $methodCall, Scope $scope): bool
    {
        return match ($typeAssertion) {
            ExpectationMatcherRegistry::TYPE_STRING => $valueType->isString()->no(),
            ExpectationMatcherRegistry::TYPE_INT => $valueType->isInteger()->no(),
            ExpectationMatcherRegistry::TYPE_FLOAT => $valueType->isFloat()->no(),
            ExpectationMatcherRegistry::TYPE_BOOL => $valueType->isTrue()->no() && $valueType->isFalse()->no(),
            ExpectationMatcherRegistry::TYPE_TRUE => $valueType->isTrue()->no(),
            ExpectationMatcherRegistry::TYPE_FALSE => $valueType->isFalse()->no(),
            ExpectationMatcherRegistry::TYPE_NULL => $valueType->isNull()->no(),
            ExpectationMatcherRegistry::TYPE_ARRAY => $valueType->isArray()->no(),
            ExpectationMatcherRegistry::TYPE_LIST => $valueType->isList()->no(),
            ExpectationMatcherRegistry::TYPE_OBJECT => $valueType->isObject()->no(),
            ExpectationMatcherRegistry::TYPE_CALLABLE => $valueType->isCallable()->no(),
            ExpectationMatcherRegistry::TYPE_ITERABLE => $valueType->isIterable()->no(),
            ExpectationMatcherRegistry::TYPE_NUMERIC => $this->isNeverNumeric($valueType),
            ExpectationMatcherRegistry::TYPE_SCALAR => $this->isNeverScalar($valueType),
            ExpectationMatcherRegistry::TYPE_INSTANCE_OF => $this->isInstanceOfImpossible($valueType, $methodCall, $scope),
            default => false,
        };
    }

    private function isRedundant(string $typeAssertion, Type $valueType, MethodCall $methodCall, Scope $scope): bool
    {
        return match ($typeAssertion) {
            ExpectationMatcherRegistry::TYPE_STRING => $valueType->isString()->yes(),
            ExpectationMatcherRegistry::TYPE_INT => $valueType->isInteger()->yes(),
            ExpectationMatcherRegistry::TYPE_FLOAT => $valueType->isFloat()->yes(),
            ExpectationMatcherRegistry::TYPE_BOOL => $valueType->isTrue()->yes() || $valueType->isFalse()->yes(),
            ExpectationMatcherRegistry::TYPE_TRUE => $valueType->isTrue()->yes(),
            ExpectationMatcherRegistry::TYPE_FALSE => $valueType->isFalse()->yes(),
            ExpectationMatcherRegistry::TYPE_NULL => $valueType->isNull()->yes(),
            ExpectationMatcherRegistry::TYPE_ARRAY => $valueType->isArray()->yes(),
            ExpectationMatcherRegistry::TYPE_LIST => $valueType->isList()->yes(),
            ExpectationMatcherRegistry::TYPE_OBJECT => $valueType->isObject()->yes(),
            ExpectationMatcherRegistry::TYPE_CALLABLE => $valueType->isCallable()->yes(),
            ExpectationMatcherRegistry::TYPE_ITERABLE => $valueType->isIterable()->yes(),
            ExpectationMatcherRegistry::TYPE_NUMERIC => $valueType->isInteger()->yes() || $valueType->isFloat()->yes(),
            ExpectationMatcherRegistry::TYPE_SCALAR => $this->isAlwaysScalar($valueType),
            ExpectationMatcherRegistry::TYPE_INSTANCE_OF => $this->isInstanceOfTrivial($valueType, $methodCall, $scope),
            default => false,
        };
    }

    private function isNeverNumeric(Type $type): bool
    {
        return $type->isInteger()->no()
            && $type->isFloat()->no()
            && $type->isString()->no();
    }

    private function isNeverScalar(Type $type): bool
    {
        return $type->isString()->no()
            && $type->isInteger()->no()
            && $type->isFloat()->no()
            && $type->isTrue()->no()
            && $type->isFalse()->no();
    }

    private function isAlwaysScalar(Type $type): bool
    {
        if ($type->isString()->yes()) {
            return true;
        }

        if ($type->isInteger()->yes()) {
            return true;
        }

        if ($type->isFloat()->yes()) {
            return true;
        }

        if ($type->isTrue()->yes()) {
            return true;
        }

        return $type->isFalse()->yes();
    }

    private function isInstanceOfImpossible(Type $valueType, MethodCall $methodCall, Scope $scope): bool
    {
        $args = $methodCall->getArgs();
        if ($args === []) {
            return false;
        }

        $classType = $scope->getType($args[0]->value);
        $classNames = $classType->getConstantStrings();

        if ($classNames === []) {
            return $valueType->isObject()->no();
        }

        foreach ($classNames as $className) {
            $assertedType = new ObjectType($className->getValue());
            if (! $valueType->isSuperTypeOf($assertedType)->no()) {
                return false;
            }
        }

        return true;
    }

    private function isInstanceOfTrivial(Type $valueType, MethodCall $methodCall, Scope $scope): bool
    {
        $args = $methodCall->getArgs();
        if ($args === []) {
            return false;
        }

        $classType = $scope->getType($args[0]->value);
        $classNames = $classType->getConstantStrings();

        if ($classNames === []) {
            return false;
        }

        foreach ($classNames as $className) {
            $assertedType = new ObjectType($className->getValue());
            if (! $assertedType->isSuperTypeOf($valueType)->yes()) {
                return false;
            }
        }

        return true;
    }
}
