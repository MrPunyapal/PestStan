<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\IgnoreErrorExtension;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use PHPUnit\Framework\TestCase;

/**
 * Suppresses method.protected errors for $this->method() calls inside Pest
 * test closures, where $this is bound to the configured TestCase class or a
 * subclass. This mirrors PHPUnit's behaviour where the generated child class
 * that Pest creates at runtime can legitimately call protected TestCase methods.
 */
final class ProtectedMethodCallIgnoreExtension implements IgnoreErrorExtension
{
    /** @param class-string $testCaseClass */
    public function __construct(
        private readonly string $testCaseClass = TestCase::class,
    ) {}

    public function shouldIgnore(Error $error, Node $node, Scope $scope): bool
    {
        if ($error->getIdentifier() !== 'method.protected') {
            return false;
        }

        if (! $scope->isInAnonymousFunction()) {
            return false;
        }

        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $node->var instanceof Variable || $node->var->name !== 'this') {
            return false;
        }

        if (! $scope->hasVariableType('this')->yes()) {
            return false;
        }

        $thisType = $scope->getVariableType('this');

        return (new ObjectType($this->testCaseClass))
            ->isSuperTypeOf($thisType)
            ->yes();
    }
}
