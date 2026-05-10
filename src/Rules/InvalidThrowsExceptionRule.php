<?php

declare(strict_types=1);

namespace PestStan\Rules;

use Pest\PendingCalls\TestCall;
use PestStan\Diagnostics\PestDiagnosticIdentifiers;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use Throwable;

/**
 * Detects throws() calls with a class that is not Throwable.
 *
 * @implements Rule<MethodCall>
 */
final class InvalidThrowsExceptionRule implements Rule
{
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
    ) {}

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->name instanceof Identifier || $node->name->name !== 'throws') {
            return [];
        }

        $callerType = $scope->getType($node->var);
        if (! (new ObjectType(TestCall::class))->isSuperTypeOf($callerType)->yes()) {
            return [];
        }

        $args = $node->getArgs();
        if ($args === []) {
            return [];
        }

        $className = $this->extractClassName($args[0]->value);
        if ($className === null) {
            return [];
        }

        if (! $this->reflectionProvider->hasClass($className)) {
            return [
                RuleErrorBuilder::message(
                    sprintf('Class %s passed to throws() does not exist.', $className)
                )
                    ->identifier(PestDiagnosticIdentifiers::THROWS_CLASS_NOT_FOUND)
                    ->build(),
            ];
        }

        $classReflection = $this->reflectionProvider->getClass($className);
        if (! $classReflection->is(Throwable::class)) {
            return [
                RuleErrorBuilder::message(
                    sprintf('throws() expects a Throwable class, got %s.', $className)
                )
                    ->identifier(PestDiagnosticIdentifiers::THROWS_INVALID_EXCEPTION)
                    ->build(),
            ];
        }

        return [];
    }

    private function extractClassName(Expr $expr): ?string
    {
        if ($expr instanceof ClassConstFetch && $expr->name instanceof Identifier && $expr->name->toString() === 'class' && $expr->class instanceof Name) {
            return $expr->class->toString();
        }

        if ($expr instanceof String_) {
            return $expr->value;
        }

        return null;
    }
}
