<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use Pest\PendingCalls\TestCall;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\IgnoreErrorExtension;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;

/**
 * Suppresses method.notFound and method.nonObject errors for fluent method
 * chains on Pest\PendingCalls\TestCall. TestCall uses __call() to proxy
 * method calls (e.g. preset()) at runtime via its @mixin annotations, but
 * PHPStan cannot resolve the union-typed @mixin declaration.
 */
final class TestCallMethodIgnoreExtension implements IgnoreErrorExtension
{
    private const SUPPORTED_IDENTIFIERS = [
        'method.notFound',
        'method.nonObject',
    ];

    public function shouldIgnore(Error $error, Node $node, Scope $scope): bool
    {
        if (! in_array($error->getIdentifier(), self::SUPPORTED_IDENTIFIERS, true)) {
            return false;
        }

        if (! $node instanceof MethodCall) {
            return false;
        }

        return $this->isTestCallChain($node, $scope);
    }

    private function isTestCallChain(MethodCall $node, Scope $scope): bool
    {
        $var = $node->var;

        while ($var instanceof MethodCall) {
            $var = $var->var;
        }

        $type = $scope->getType($var);

        return (new ObjectType(TestCall::class))
            ->isSuperTypeOf($type)
            ->yes();
    }
}
