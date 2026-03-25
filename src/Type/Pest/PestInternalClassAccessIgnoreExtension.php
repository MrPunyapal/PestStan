<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\IgnoreErrorExtension;
use PHPStan\Analyser\Scope;

final class PestInternalClassAccessIgnoreExtension implements IgnoreErrorExtension
{
    public function shouldIgnore(Error $error, Node $node, Scope $scope): bool
    {
        $identifier = $error->getIdentifier();

        if ($identifier === 'property.internalClass' && $node instanceof PropertyFetch) {
            return $this->isPestInternalType($scope->getType($node->var)->getReferencedClasses());
        }

        if ($identifier === 'method.internalClass' && $node instanceof MethodCall) {
            return $this->isPestInternalType($scope->getType($node->var)->getReferencedClasses());
        }

        return false;
    }

    /** @param list<class-string> $classes */
    private function isPestInternalType(array $classes): bool
    {
        foreach ($classes as $class) {
            if (str_starts_with($class, 'Pest\\')) {
                return true;
            }
        }

        return false;
    }
}
